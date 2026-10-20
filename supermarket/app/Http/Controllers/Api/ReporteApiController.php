<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VentaResource;
use App\Models\Venta;
use Illuminate\Http\Request;

class ReporteApiController extends Controller
{
    // GET /api/reportes/ventas?fecha_inicio=&fecha_fin=&id_cliente=&estado=
    // Mismos filtros y mismas 4 métricas que ReporteController@ventas (admin web).
    public function ventas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            'id_cliente'   => 'nullable|exists:clientes,idCliente',
            'estado'       => 'nullable|in:todos,completado,anulado',
        ]);

        $query = Venta::with(['cliente', 'usuario', 'detalles.producto']);

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        } elseif ($request->filled('fecha_inicio')) {
            $query->where('fecha', '>=', $request->fecha_inicio);
        } elseif ($request->filled('fecha_fin')) {
            $query->where('fecha', '<=', $request->fecha_fin);
        }

        if ($request->filled('id_cliente')) {
            $query->where('idCliente', $request->id_cliente);
        }

        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        // Métricas calculadas en SQL directamente sobre el conjunto filtrado
        $ventasValidosQuery = (clone $query)->where('estado', '!=', 'anulado');

        $cantidadVentas = (clone $query)->count();
        $totalVentas   = (float) ($ventasValidosQuery->sum('total') ?? 0);
        $cantValidos   = $ventasValidosQuery->count();
        $promedioVenta = $cantValidos > 0 ? round($totalVentas / $cantValidos, 2) : 0;

        // Sumatoria de unidades vendidas en SQL mediante subconsulta
        $totalUnidades = (int) \App\Models\DetalleVenta::whereIn('idVenta', (clone $ventasValidosQuery)->select('idVenta'))
            ->sum('cantidad');

        $ventas = $query->orderBy('fecha', 'desc')->paginate(20);
        $ventasSerializadas = VentaResource::collection($ventas)->response()->getData(true);

        return response()->json([
            'metricas' => [
                'cantidad_ventas' => $cantidadVentas,
                'total_ventas'    => $totalVentas,
                'total_unidades' => $totalUnidades,
                'promedio_venta' => $promedioVenta,
            ],
            'ventas' => $ventasSerializadas,
        ]);
    }
}
