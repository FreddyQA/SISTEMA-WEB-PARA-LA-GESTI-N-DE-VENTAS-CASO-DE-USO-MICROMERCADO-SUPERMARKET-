[CmdletBinding()]
param(
    [string]$BaseUrl = 'http://127.0.0.1:8000',
    [int]$ChromePort = 9223,
    [string]$OutputDirectory = (Join-Path $PSScriptRoot '..\storage\app\lighthouse-audit'),
    [string]$BaselineDirectory,
    [string]$EditId = '1',
    [ValidateSet('mobile', 'desktop')]
    [string[]]$Devices = @('mobile', 'desktop'),
    [string[]]$PageNames = @(),
    [switch]$FailOnRegression
)

$ErrorActionPreference = 'Stop'
$BaseUrl = $BaseUrl.TrimEnd('/')
$OutputDirectory = [System.IO.Path]::GetFullPath($OutputDirectory)
New-Item -ItemType Directory -Force -Path $OutputDirectory | Out-Null

$pages = @(
    [pscustomobject]@{ Name = 'login'; BaselineName = 'login'; Path = '/login'; RequiresSession = $false },
    [pscustomobject]@{ Name = 'dashboard'; BaselineName = 'dashboard'; Path = '/dashboard'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'productos'; BaselineName = 'productos'; Path = '/productos'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'productos-create'; BaselineName = 'productos-create'; Path = '/productos/create'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'productos-edit'; BaselineName = 'productos-1-edit'; Path = "/productos/$EditId/edit"; RequiresSession = $true },
    [pscustomobject]@{ Name = 'categorias'; BaselineName = 'categorias'; Path = '/categorias'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'categorias-create'; BaselineName = 'categorias-create'; Path = '/categorias/create'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'categorias-edit'; BaselineName = 'categorias-1-edit'; Path = "/categorias/$EditId/edit"; RequiresSession = $true },
    [pscustomobject]@{ Name = 'clientes'; BaselineName = 'clientes'; Path = '/clientes'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'clientes-create'; BaselineName = 'clientes-create'; Path = '/clientes/create'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'clientes-edit'; BaselineName = 'clientes-1-edit'; Path = "/clientes/$EditId/edit"; RequiresSession = $true },
    [pscustomobject]@{ Name = 'ventas'; BaselineName = 'pedidos'; Path = '/ventas'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'ventas-create'; BaselineName = 'pedidos-create'; Path = '/ventas/create'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'ventas-edit'; BaselineName = 'pedidos-1-edit'; Path = "/ventas/$EditId/edit"; RequiresSession = $true },
    [pscustomobject]@{ Name = 'reportes-ventas'; BaselineName = 'reportes-ventas'; Path = '/reportes/ventas'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'usuarios'; BaselineName = 'usuarios'; Path = '/usuarios'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'usuarios-create'; BaselineName = 'usuarios-create'; Path = '/usuarios/create'; RequiresSession = $true },
    [pscustomobject]@{ Name = 'usuarios-edit'; BaselineName = 'usuarios-1-edit'; Path = "/usuarios/$EditId/edit"; RequiresSession = $true },
    [pscustomobject]@{ Name = 'papelera'; BaselineName = 'papelera'; Path = '/papelera'; RequiresSession = $true }
)

if ($PageNames.Count -gt 0) {
    $unknownPages = @($PageNames | Where-Object { $_ -notin $pages.Name })
    if ($unknownPages.Count -gt 0) {
        throw "Páginas desconocidas: $($unknownPages -join ', ')."
    }
    $pages = @($pages | Where-Object { $_.Name -in $PageNames })
}

function Clear-ChromeCache([int]$Port) {
    $response = Invoke-WebRequest "http://127.0.0.1:$Port/json/list" -UseBasicParsing -TimeoutSec 5
    $targets = $response.Content | ConvertFrom-Json
    $target = $targets | Where-Object { $_.type -eq 'page' -and $_.webSocketDebuggerUrl } | Select-Object -First 1
    if (-not $target) {
        throw "No se encontró una pestaña de Chrome en el puerto $Port."
    }

    $socket = [System.Net.WebSockets.ClientWebSocket]::new()
    $token = [System.Threading.CancellationToken]::None
    try {
        $socket.ConnectAsync([uri]$target.webSocketDebuggerUrl, $token).GetAwaiter().GetResult()
        $payload = [System.Text.Encoding]::UTF8.GetBytes('{"id":1,"method":"Network.clearBrowserCache"}')
        $socket.SendAsync(
            [System.ArraySegment[byte]]::new($payload),
            [System.Net.WebSockets.WebSocketMessageType]::Text,
            $true,
            $token
        ).GetAwaiter().GetResult() | Out-Null

        $buffer = New-Object byte[] 4096
        $responseText = ''
        do {
            $received = $socket.ReceiveAsync([System.ArraySegment[byte]]::new($buffer), $token).GetAwaiter().GetResult()
            $responseText += [System.Text.Encoding]::UTF8.GetString($buffer, 0, $received.Count)
        } while (-not $received.EndOfMessage)

        $commandResult = $responseText | ConvertFrom-Json
        if ($commandResult.error) {
            throw "No se pudo limpiar la caché de Chrome: $($commandResult.error.message)"
        }
    } finally {
        if ($socket.State -eq [System.Net.WebSockets.WebSocketState]::Open) {
            $socket.CloseAsync(
                [System.Net.WebSockets.WebSocketCloseStatus]::NormalClosure,
                'done',
                $token
            ).GetAwaiter().GetResult() | Out-Null
        }
    }
}

$categories = @('performance', 'accessibility', 'best-practices', 'seo')
$results = [System.Collections.Generic.List[object]]::new()
$regressions = [System.Collections.Generic.List[string]]::new()

foreach ($device in $Devices) {
    foreach ($page in $pages) {
        $reportPath = Join-Path $OutputDirectory "$($page.Name)-$device.json"
        $url = "$BaseUrl$($page.Path)"
        $arguments = @(
            '--yes',
            'lighthouse@12.8.2',
            $url,
            "--form-factor=$device",
            '--only-categories=performance,accessibility,best-practices,seo',
            '--output=json',
            "--output-path=$reportPath",
            '--quiet'
        )

        if ($device -eq 'desktop') {
            $arguments += '--preset=desktop'
        }

        if ($page.RequiresSession) {
            $arguments += @("--port=$ChromePort", '--disable-storage-reset')
        }

        Write-Host "Auditando $($page.Name) ($device)"
        & npx.cmd @arguments
        if ($LASTEXITCODE -ne 0) {
            throw "Lighthouse falló para $url ($device), código $LASTEXITCODE."
        }

        $report = Get-Content -Raw $reportPath | ConvertFrom-Json
        if ($report.finalDisplayedUrl -ne $url) {
            throw "La ruta $url terminó en $($report.finalDisplayedUrl); verifica que no haya redirigido, por ejemplo, al login."
        }
        $documentRequest = $report.audits.'network-requests'.details.items |
            Where-Object { $_.resourceType -eq 'Document' -and $_.url -eq $report.finalDisplayedUrl } |
            Select-Object -First 1
        if (-not $documentRequest) {
            throw "No se pudo verificar el estado HTTP de $($report.finalDisplayedUrl)."
        }
        if ($documentRequest.statusCode -ne 200) {
            throw "La URL $($report.finalDisplayedUrl) respondió HTTP $($documentRequest.statusCode), se esperaba 200."
        }

        $row = [ordered]@{
            Page = $page.Name
            Device = $device
            Status = $documentRequest.statusCode
        }

        foreach ($category in $categories) {
            $score = [math]::Round($report.categories.$category.score * 100)
            $row[$category] = $score

            if ($BaselineDirectory) {
                $baselineCandidates = @(
                    (Join-Path $BaselineDirectory "$($page.Name)-$device.json"),
                    (Join-Path $BaselineDirectory "lighthouse-$($page.BaselineName)-$device-cold-baseline.json")
                )
                $baselinePath = $baselineCandidates | Where-Object { Test-Path $_ } | Select-Object -First 1
                if ($baselinePath) {
                    $baseline = Get-Content -Raw $baselinePath | ConvertFrom-Json
                    $baselineScore = [math]::Round($baseline.categories.$category.score * 100)
                    $row["$category-delta"] = $score - $baselineScore
                    if ($score -lt $baselineScore) {
                        $regressions.Add("$($page.Name) ($device): $category $baselineScore -> $score")
                    }
                } else {
                    $row["$category-delta"] = ''
                }
            }
        }

        $results.Add([pscustomobject]$row)
    }
}

$results | Format-Table -AutoSize
$results | Export-Csv -NoTypeInformation -Encoding UTF8 -Path (Join-Path $OutputDirectory 'summary.csv')

if ($regressions.Count -gt 0) {
    Write-Warning ("Regresiones frente a la línea base:`n - " + ($regressions -join "`n - "))
    if ($FailOnRegression) {
        exit 1
    }
}
