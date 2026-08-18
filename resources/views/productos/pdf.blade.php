<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario de Productos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%);
            color: #ffffff;
            padding: 18px 24px 14px;
            margin-bottom: 16px;
            border-radius: 6px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -0.02em;
            margin-bottom: 3px;
        }
        .header .subtitle {
            font-size: 10px;
            opacity: 0.80;
        }
        .header .meta {
            font-size: 9.5px;
            opacity: 0.72;
            margin-top: 2px;
        }

        /* ── KPI Cards row ── */
        .kpi-row {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border-spacing: 8px 0;
        }
        .kpi-card {
            display: table-cell;
            width: 16.6%;
            padding: 10px 12px;
            border-radius: 8px;
            vertical-align: middle;
            text-align: center;
        }
        .kpi-card .kpi-num {
            font-size: 18px;
            font-weight: 900;
            line-height: 1.1;
            display: block;
        }
        .kpi-card .kpi-label {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            opacity: 0.85;
            margin-top: 2px;
            display: block;
        }
        .kpi-blue   { background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; }
        .kpi-violet { background: #f5f3ff; color: #6d28d9; border: 1.5px solid #ddd6fe; }
        .kpi-green  { background: #f0fdf4; color: #15803d; border: 1.5px solid #bbf7d0; }
        .kpi-yellow { background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; }
        .kpi-red    { background: #fef2f2; color: #b91c1c; border: 1.5px solid #fecaca; }
        .kpi-gray   { background: #f8fafc; color: #475569; border: 1.5px solid #e2e8f0; }

        /* ── Category summary ── */
        .section-title {
            font-size: 11px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-left: 3px solid #4f46e5;
            padding-left: 8px;
            margin-bottom: 8px;
            margin-top: 14px;
        }

        .cat-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .cat-table th {
            background: #f1f5f9;
            color: #475569;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 5px 8px;
            text-align: left;
            border-bottom: 1.5px solid #e2e8f0;
        }
        .cat-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }
        .cat-table .text-right { text-align: right; }
        .cat-table .text-center { text-align: center; }

        /* ── Main products table ── */
        table.main {
            width: 100%;
            border-collapse: collapse;
        }
        table.main thead tr {
            background: #1e293b;
        }
        table.main thead th {
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 7px 8px;
            text-align: left;
            border: none;
        }
        table.main thead th.text-center { text-align: center; }
        table.main thead th.text-right  { text-align: right; }

        table.main tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        table.main tbody td {
            padding: 5px 8px;
            font-size: 9.5px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        table.main tbody td.text-center { text-align: center; }
        table.main tbody td.text-right  { text-align: right; }

        /* Category separator row */
        tr.cat-sep td {
            background: #eff6ff !important;
            color: #1d4ed8;
            font-weight: 800;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 4px 8px;
        }

        /* Stock badges */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .03em;
        }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red    { background: #fee2e2; color: #b91c1c; }

        /* SKU pill */
        .sku {
            font-family: monospace;
            font-size: 8.5px;
            color: #64748b;
        }

        /* Footer */
        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    {{-- ══ HEADER ══ --}}
    <div class="header">
        <h1>Reporte de Inventario de Productos</h1>
        <div class="subtitle">Supermarket — Sistema de Gestión Comercial</div>
        <div class="meta">Generado el {{ now()->format('d/m/Y H:i') }} &nbsp;·&nbsp; Total registros: {{ $totalProductos }}</div>
    </div>

    {{-- ══ KPI CARDS ══ --}}
    <div class="kpi-row">
        <div class="kpi-card kpi-blue">
            <span class="kpi-num">{{ $totalProductos }}</span>
            <span class="kpi-label">Productos</span>
        </div>
        <div class="kpi-card kpi-violet">
            <span class="kpi-num">{{ number_format($precioPromedio, 2) }}</span>
            <span class="kpi-label">Precio Prom. (Bs)</span>
        </div>
        <div class="kpi-card kpi-green">
            <span class="kpi-num">{{ $totalStock }}</span>
            <span class="kpi-label">Unidades en Stock</span>
        </div>
        <div class="kpi-card kpi-gray">
            <span class="kpi-num">{{ number_format($inventarioValuado, 0) }}</span>
            <span class="kpi-label">Valor Inventario (Bs)</span>
        </div>
        <div class="kpi-card kpi-yellow">
            <span class="kpi-num">{{ $stockCritico }}</span>
            <span class="kpi-label">Stock Crítico (≤5)</span>
        </div>
        <div class="kpi-card kpi-red">
            <span class="kpi-num">{{ $stockAgotado }}</span>
            <span class="kpi-label">Agotados</span>
        </div>
    </div>

    {{-- ══ RESUMEN POR CATEGORÍA ══ --}}
    <div class="section-title">Resumen por Categoría</div>
    <table class="cat-table">
        <thead>
            <tr>
                <th>Categoría</th>
                <th class="text-center">Productos</th>
                <th class="text-center">Stock Total</th>
                <th class="text-right">Valor en Inventario (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porCategoria->sortKeys() as $catNombre => $datos)
                <tr>
                    <td><strong>{{ $catNombre }}</strong></td>
                    <td class="text-center">{{ $datos['total'] }}</td>
                    <td class="text-center">{{ $datos['stock'] }}</td>
                    <td class="text-right"><strong>Bs {{ number_format($datos['valor'], 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ══ TABLA PRINCIPAL ══ --}}
    <div class="section-title">Detalle Completo de Productos</div>
    <table class="main">
        <thead>
            <tr>
                <th style="width:32px;" class="text-center">N°</th>
                <th style="width:68px;">SKU</th>
                <th>Nombre del Producto</th>
                <th style="width:90px;">Categoría</th>
                <th style="width:68px;" class="text-right">Precio (Bs)</th>
                <th style="width:45px;" class="text-center">Stock</th>
                <th style="width:80px;" class="text-center">Estado</th>
                <th style="width:90px;" class="text-right">Valor (Bs)</th>
                <th style="width:68px;" class="text-center">Registro</th>
            </tr>
        </thead>
        <tbody>
            @php $prevCat = null; $n = 0; @endphp
            @foreach($productos as $p)
                @php
                    $catNombre = $p->categoria->nombre ?? 'Sin Categoría';
                    $sku       = 'PRD-' . str_pad($p->idProducto, 5, '0', STR_PAD_LEFT);
                    $valor     = $p->precio * $p->stock;
                    $n++;
                @endphp

                {{-- Category separator --}}
                @if($catNombre !== $prevCat)
                    @php $prevCat = $catNombre; @endphp
                    <tr class="cat-sep">
                        <td colspan="9">&#9658; {{ $catNombre }}</td>
                    </tr>
                @endif

                <tr>
                    <td class="text-center" style="color:#94a3b8;">{{ $n }}</td>
                    <td><span class="sku">{{ $sku }}</span></td>
                    <td><strong>{{ $p->nombre }}</strong></td>
                    <td>{{ $catNombre }}</td>
                    <td class="text-right">{{ number_format($p->precio, 2) }}</td>
                    <td class="text-center"><strong>{{ $p->stock }}</strong></td>
                    <td class="text-center">
                        @if($p->stock == 0)
                            <span class="badge badge-red">Agotado</span>
                        @elseif($p->stock <= 5)
                            <span class="badge badge-yellow">Crítico</span>
                        @else
                            <span class="badge badge-green">Disponible</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($valor, 2) }}</td>
                    <td class="text-center" style="color:#64748b;">
                        {{ $p->created_at ? $p->created_at->format('d/m/Y') : '—' }}
                    </td>
                </tr>
            @endforeach

            {{-- Total row --}}
            <tr>
                <td colspan="4" style="font-weight:800; text-align:right; background:#f1f5f9; padding:7px 8px; border-top:2px solid #e2e8f0;">
                    TOTALES GENERALES
                </td>
                <td style="font-weight:800; text-align:right; background:#f1f5f9; padding:7px 8px; border-top:2px solid #e2e8f0;"></td>
                <td style="font-weight:800; text-align:center; background:#f1f5f9; padding:7px 8px; border-top:2px solid #e2e8f0;">{{ $totalStock }}</td>
                <td style="background:#f1f5f9; border-top:2px solid #e2e8f0;"></td>
                <td style="font-weight:800; text-align:right; background:#fef3c7; padding:7px 8px; border-top:2px solid #e2e8f0; color:#92400e;">
                    Bs {{ number_format($inventarioValuado, 2) }}
                </td>
                <td style="background:#f1f5f9; border-top:2px solid #e2e8f0;"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Supermarket &copy; {{ date('Y') }} — Reporte generado automáticamente. Todos los precios en Bolivianos (Bs).
    </div>

</body>
</html>