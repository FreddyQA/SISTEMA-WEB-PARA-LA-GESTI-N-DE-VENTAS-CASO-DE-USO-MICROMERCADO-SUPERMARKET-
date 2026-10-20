<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoRequest;
use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    // LISTAR
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'usuario', 'detalles.producto'])
            ->latest('fecha')
            ->get();

        return view('pedidos.index', compact('pedidos'));
    }

    // FORM CREAR
    public function create()
    {
        $clientes  = Cliente::orderBy('nombre')->get();
        $productos = Producto::where('stock', '>', 0)->orderBy('nombre')->get();

        return view('pedidos.create', compact('clientes', 'productos'));
    }

    // GUARDAR
    public function store(StorePedidoRequest $request)
    {
        $datos = $request->validated();

        $pedido = DB::transaction(function () use ($datos) {
            $pedido = Pedido::create([
                'idCliente' => $datos['idCliente'],
                'idUsuario' => auth()->id(),
                'fecha'     => $datos['fecha'],
                'estado'    => 'completado',
                'total'     => 0,
            ]);

            $total = $this->registrarDetalles($pedido, $datos['items']);

            $pedido->update(['total' => $total]);

            return $pedido;
        });

        return redirect()->route('pedidos.index')
            ->with('success', "Pedido #{$pedido->idPedido} registrado correctamente.");
    }

    // FORM EDITAR
    public function edit($id)
    {
        $pedido    = Pedido::with('detalles')->findOrFail($id);
        $clientes  = Cliente::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();

        return view('pedidos.edit', compact('pedido', 'clientes', 'productos'));
    }

    // ACTUALIZAR
    public function update(UpdatePedidoRequest $request, $id)
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos, $id) {
            $pedido = Pedido::with('detalles')->lockForUpdate()->findOrFail($id);

            // Devolver al stock lo que este pedido tenía reservado antes de editar
            foreach ($pedido->detalles as $detalleAnterior) {
                Producto::where('idProducto', $detalleAnterior->idProducto)
                    ->increment('stock', $detalleAnterior->cantidad);
            }
            $pedido->detalles()->delete();

            $total = $this->registrarDetalles($pedido, $datos['items']);

            $pedido->update([
                'idCliente' => $datos['idCliente'],
                'fecha'     => $datos['fecha'],
                'estado'    => $datos['estado'],
                'total'     => $total,
            ]);
        });

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido actualizado correctamente.');
    }

    // ELIMINAR (soft delete + devolución de stock)
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $pedido = Pedido::with('detalles')->lockForUpdate()->findOrFail($id);

            foreach ($pedido->detalles as $detalle) {
                Producto::where('idProducto', $detalle->idProducto)
                    ->increment('stock', $detalle->cantidad);
            }

            $pedido->delete();
        });

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido eliminado y stock restituido.');
    }

    /**
     * Crea las líneas de detalle para un pedido, validando y
     * descontando stock de forma atómica (lockForUpdate evita que dos
     * ventas simultáneas vendan el mismo stock dos veces).
     *
     * @param  array<int, array{idProducto:int, cantidad:int}> $items
     * @return string total del pedido (suma de subtotales)
     */
    private function registrarDetalles(Pedido $pedido, array $items): string
    {
        $total = '0';

        foreach ($items as $item) {
            $producto = Producto::where('idProducto', $item['idProducto'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($producto->stock < $item['cantidad']) {
                throw ValidationException::withMessages([
                    'items' => "Stock insuficiente para \"{$producto->nombre}\" (disponible: {$producto->stock}).",
                ]);
            }

            $subtotal = bcmul((string) $producto->precio, (string) $item['cantidad'], 2);

            DetallePedido::create([
                'idPedido'        => $pedido->idPedido,
                'idProducto'      => $producto->idProducto,
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $producto->precio,
                'subtotal'        => $subtotal,
            ]);

            $producto->decrement('stock', $item['cantidad']);

            $total = bcadd($total, $subtotal, 2);
        }

        return $total;
    }
}
