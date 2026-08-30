<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
        }
        .header-box {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        h2 {
            margin: 0 0 6px 0;
            color: #1e1b4b;
            font-size: 20px;
        }
        .filtros-info {
            font-size: 11px;
            color: #475569;
        }
        .filtros-info span {
            margin: 0 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background: #1e293b;
            color: #ffffff;
            border: 1px solid #0f172a;
            padding: 7px 8px;
            font-size: 10.5px;
            text-align: left;
        }
        table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10px;
        }
        table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .total-box {
            margin-top: 15px;
            padding: 10px 14px;
            background: #f1f5f9;
            border-radius: 6px;
            text-align: right;
            font-size: 13px;
        }
        .total-box strong {
            font-size: 15px;
            color: #0f172a;
        }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header-box">
        <h2>Reporte Detallado de Ventas</h2>
        <div class="filtros-info">
            @if(!empty($inicio) && !empty($fin))
                <span><strong>Período:</strong> {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }}</span>
            @elseif(!empty($inicio))
                <span><strong>Desde:</strong> {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }}</span>
            @elseif(!empty($fin))
                <span><strong>Hasta:</strong> {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }}</span>
            @else
                <span><strong>Período:</strong> Historial Completo</span>
            @endif

            @if(!empty($clienteFiltro))
                <span>| <strong>Cliente:</strong> {{ $clienteFiltro->nombre }}</span>
            @endif

            @if(!empty($estado) && $estado !== 'todos')
                <span>| <strong>Estado:</strong> {{ ucfirst($estado) }}</span>
            @endif

            <span>| <strong>Generado el:</strong> {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 45px;" class="text-center">ID</th>
                <th style="width: 140px;">Cliente</th>
                <th>Productos</th>
                <th style="width: 55px;" class="text-center">Cant.</th>
                <th style="width: 75px;" class="text-center">Estado</th>
                <th style="width: 80px;" class="text-right">Total (Bs)</th>
                <th style="width: 75px;" class="text-center">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                <tr>
                    <td class="text-center">#{{ $pedido->idPedido }}</td>
                    <td>{{ $pedido->cliente->nombre ?? '—' }}</td>
                    <td>
                        @foreach($pedido->detalles as $d)
                            {{ $d->producto->nombre ?? 'Producto eliminado' }} (x{{ $d->cantidad }})@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td class="text-center">{{ $pedido->detalles->sum('cantidad') }}</td>
                    <td class="text-center">
                        @if($pedido->estado === 'anulado')
                            <span class="badge badge-danger">Anulado</span>
                        @else
                            <span class="badge badge-success">Completado</span>
                        @endif
                    </td>
                    <td class="text-right">
                        {{ number_format($pedido->total, 2) }}
                    </td>
                    <td class="text-center">
                        {{ $pedido->fecha ? \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #64748b;">
                        No se encontraron registros para los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-box">
        Total Pedidos: <strong>{{ count($pedidos) }}</strong> &nbsp;|&nbsp;
        Total Venta Válida: <strong>Bs {{ number_format($totalGeneral, 2) }}</strong>
    </div>

    <div class="footer">
        Supermarket — Sistema de Gestión Comercial
    </div>

</body>
</html>