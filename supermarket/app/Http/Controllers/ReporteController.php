<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Cliente;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentasExport;

class ReporteController extends Controller
{
    public function ventas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            'id_cliente'   => 'nullable|exists:clientes,idCliente',
            'estado'       => 'nullable|in:todos,completado,anulado',
        ], [
            'fecha_inicio.date'        => 'La fecha de inicio no es válida.',
            'fecha_fin.date'           => 'La fecha final no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            'id_cliente.exists'        => 'El cliente seleccionado no es válido.',
        ]);

        $query = Venta::with(['cliente', 'detalles.producto']);

        // Filtro por Fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [
                $request->fecha_inicio,
                $request->fecha_fin,
            ]);
        } elseif ($request->filled('fecha_inicio')) {
            $query->where('fecha', '>=', $request->fecha_inicio);
        } elseif ($request->filled('fecha_fin')) {
            $query->where('fecha', '<=', $request->fecha_fin);
        }

        // Filtro por Cliente
        if ($request->filled('id_cliente')) {
            $query->where('idCliente', $request->id_cliente);
        }

        // Filtro por Estado
        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        // Métricas calculadas en SQL directamente sobre el conjunto filtrado (alto rendimiento, O(1) memoria)
        $ventasValidosQuery = (clone $query)->where('estado', '!=', 'anulado');

        $cantidadVentas = (clone $query)->count();
        $totalVentas   = (float) ($ventasValidosQuery->sum('total') ?? 0);
        $cantValidos   = $ventasValidosQuery->count();
        $promedioVenta = $cantValidos > 0 ? round($totalVentas / $cantValidos, 2) : 0;

        // Sumatoria de unidades vendidas en SQL mediante subconsulta
        $totalUnidades = (int) \App\Models\DetalleVenta::whereIn('idVenta', (clone $ventasValidosQuery)->select('idVenta'))
            ->sum('cantidad');

        // Tabla paginada (conserva los filtros aplicados al cambiar de página)
        $ventas = $query->orderBy('fecha', 'desc')->paginate(15)->withQueryString();

        $clientes = Cliente::orderBy('nombre')->get(['idCliente', 'nombre']);

        return view('reportes.ventas', compact(
            'ventas',
            'cantidadVentas',
            'totalVentas',
            'totalUnidades',
            'promedioVenta',
            'clientes'
        ));
    }

    public function exportPDF(Request $request)
    {
        $inicio    = $request->input('fecha_inicio') ?? $request->input('inicio');
        $fin       = $request->input('fecha_fin') ?? $request->input('fin');
        $idCliente = $request->input('id_cliente');
        $estado    = $request->input('estado');

        $query = Venta::with(['cliente', 'detalles.producto']);

        if ($inicio && $fin) {
            $query->whereBetween('fecha', [$inicio, $fin]);
        } elseif ($inicio) {
            $query->where('fecha', '>=', $inicio);
        } elseif ($fin) {
            $query->where('fecha', '<=', $fin);
        }

        if ($idCliente) {
            $query->where('idCliente', $idCliente);
        }

        if ($estado && $estado !== 'todos') {
            $query->where('estado', $estado);
        }

        $ventas = $query->orderBy('fecha', 'desc')->get();
        $totalGeneral = (float) $ventas->where('estado', '!=', 'anulado')->sum('total');
        $clienteFiltro = $idCliente ? Cliente::find($idCliente) : null;

        $pdf = Pdf::loadView('reportes.ventas_pdf', compact(
            'ventas',
            'inicio',
            'fin',
            'totalGeneral',
            'clienteFiltro',
            'estado'
        ));
        $pdf->setPaper('A4', 'landscape');

        $nombreArchivo = 'ventas_' . ($inicio ? $inicio : 'completo') . '_' . ($fin ? $fin : now()->format('Ymd')) . '.pdf';
        return $pdf->download($nombreArchivo);
    }

    public function exportExcel(Request $request)
    {
        $inicio    = $request->input('fecha_inicio') ?? $request->input('inicio');
        $fin       = $request->input('fecha_fin') ?? $request->input('fin');
        $idCliente = $request->input('id_cliente') ? (int)$request->input('id_cliente') : null;
        $estado    = $request->input('estado');

        $nombreArchivo = 'ventas_' . ($inicio ? $inicio : 'completo') . '_' . ($fin ? $fin : now()->format('Ymd')) . '.xlsx';

        return Excel::download(
            new VentasExport($inicio, $fin, $idCliente, $estado),
            $nombreArchivo
        );
    }
}
