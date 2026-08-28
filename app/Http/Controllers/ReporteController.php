<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
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

        $query = Pedido::with(['cliente', 'detalles.producto']);

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

        $pedidos = $query->orderBy('fecha', 'desc')->get();

        // Métricas
        $pedidosValidos = $pedidos->where('estado', '!=', 'anulado');
        $totalVentas    = (float) $pedidosValidos->sum('total');
        $totalPedidos   = $pedidos->count();
        $totalUnidades  = (int) $pedidos->flatMap->detalles->sum('cantidad');
        $promedioVenta  = $pedidosValidos->count() > 0 ? ($totalVentas / $pedidosValidos->count()) : 0;

        $clientes = Cliente::orderBy('nombre')->get(['idCliente', 'nombre']);

        return view('reportes.ventas', compact(
            'pedidos',
            'totalVentas',
            'totalPedidos',
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

        $query = Pedido::with(['cliente', 'detalles.producto']);

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

        $pedidos = $query->orderBy('fecha', 'desc')->get();
        $totalGeneral = (float) $pedidos->where('estado', '!=', 'anulado')->sum('total');
        $clienteFiltro = $idCliente ? Cliente::find($idCliente) : null;

        $pdf = Pdf::loadView('reportes.ventas_pdf', compact(
            'pedidos',
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
