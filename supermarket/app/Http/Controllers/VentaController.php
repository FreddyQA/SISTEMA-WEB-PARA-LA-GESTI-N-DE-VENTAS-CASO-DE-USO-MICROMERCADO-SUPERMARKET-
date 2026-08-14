<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Services\VentaService;

class VentaController extends Controller
{
    public function __construct(private VentaService $ventas)
    {
    }

    // Lista las ventas con sus relaciones para que DataTables busque y ordene sin consultas adicionales,
    // ordenación por columnas y paginación ("Anterior" / "Siguiente") en el cliente.
    public function index()
    {
        $ventas = Venta::with(['cliente', 'usuario', 'detalles.producto'])
            ->latest('fecha')
            ->get();

        return view('ventas.index', compact('ventas'));
    }

    // FORM CREAR
    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();

        return view('ventas.create', compact('clientes'));
    }

    // GUARDAR
    public function store(StoreVentaRequest $request)
    {
        $venta = $this->ventas->crear($request->validated(), auth()->id());

        return redirect()->route('ventas.index')
            ->with('success', "Venta #{$venta->numero} registrada correctamente.");
    }

    // FORM EDITAR
    public function edit($id)
    {
        $venta   = Venta::with(['detalles.producto.lotes'])->findOrFail($id);
        $clientes = Cliente::orderBy('nombre')->get();
        $itemsIniciales = $venta->detalles->map(static fn ($detalle) => [
            'id' => $detalle->idProducto,
            'nombre' => $detalle->producto?->nombre ?? ('Producto #' . $detalle->idProducto),
            'precio' => (float) $detalle->precio_unitario,
            'stock' => max(
                (int) ($detalle->producto?->stock_disponible ?? 0),
                (int) $detalle->cantidad,
            ),
            'cantidad' => (int) $detalle->cantidad,
        ])->values()->all();

        return view('ventas.edit', compact('venta', 'clientes', 'itemsIniciales'));
    }

    // ACTUALIZAR
    public function update(UpdateVentaRequest $request, $id)
    {
        $this->ventas->actualizar((int) $id, $request->validated());

        return redirect()->route('ventas.index')
            ->with('success', 'Venta actualizada correctamente.');
    }

    // ELIMINAR (soft delete + devolución de stock solo si estaba completado)
    public function destroy($id)
    {
        $this->ventas->eliminar((int) $id);

        return redirect()->route('ventas.index')
            ->with('success', 'Venta eliminada correctamente.');
    }
}
