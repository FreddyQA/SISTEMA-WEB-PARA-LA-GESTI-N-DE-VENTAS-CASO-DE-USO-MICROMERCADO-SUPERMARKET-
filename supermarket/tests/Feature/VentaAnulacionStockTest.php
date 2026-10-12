<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaAnulacionStockTest extends TestCase
{
    use RefreshDatabase;

    protected function escenario(int $stockInicial = 20): array
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'rol' => 'administrador']);
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Producto Test', 'precio' => 10, 'stock' => $stockInicial, 'idCategoria' => $cat->idCategoria]);
        $prod->lotes()->create(['cantidad' => $stockInicial, 'fecha_vencimiento' => null]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente@test.com']);

        return compact('admin', 'cat', 'prod', 'cliente');
    }

    protected function crearVentaCompletado($admin, $cliente, $prod, int $cantidad): Venta
    {
        $venta = Venta::create([
            'idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id,
            'fecha' => now(), 'estado' => 'completado', 'total' => $prod->precio * $cantidad,
        ]);
        $venta->detalles()->create([
            'idProducto' => $prod->idProducto, 'cantidad' => $cantidad,
            'precio_unitario' => $prod->precio, 'subtotal' => $prod->precio * $cantidad,
        ]);
        $prod->decrement('stock', $cantidad);
        $prod->lotes()->first()->decrement('cantidad', $cantidad);

        return $venta;
    }

    public function test_completado_a_anulado_devuelve_stock_y_no_lo_vuelve_a_descontar()
    {
        ['admin' => $admin, 'prod' => $prod, 'cliente' => $cliente] = $this->escenario(20);
        $venta = $this->crearVentaCompletado($admin, $cliente, $prod, 5);
        $this->assertEquals(15, $prod->fresh()->stock);

        $this->actingAs($admin)->put("/ventas/{$venta->idVenta}", [
            'idCliente' => $cliente->idCliente,
            'estado' => 'anulado',
            'fecha' => now()->format('Y-m-d'),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 5]],
        ])->assertRedirect('/ventas');

        // El stock debe volver exactamente al original, ni más ni menos.
        $this->assertEquals(20, $prod->fresh()->stock);
        $this->assertEquals('anulado', $venta->fresh()->estado);
    }

    public function test_anulado_a_anulado_no_toca_el_stock()
    {
        ['admin' => $admin, 'prod' => $prod, 'cliente' => $cliente] = $this->escenario(20);
        $venta = Venta::create([
            'idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id,
            'fecha' => now(), 'estado' => 'anulado', 'total' => 50,
        ]);
        $venta->detalles()->create([
            'idProducto' => $prod->idProducto, 'cantidad' => 5,
            'precio_unitario' => $prod->precio, 'subtotal' => 50,
        ]);
        // stock sigue en 20: nunca se descontó porque nació anulado

        $this->actingAs($admin)->put("/ventas/{$venta->idVenta}", [
            'idCliente' => $cliente->idCliente,
            'estado' => 'anulado',
            'fecha' => now()->format('Y-m-d'),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 5]],
        ])->assertRedirect('/ventas');

        $this->assertEquals(20, $prod->fresh()->stock);
    }

    public function test_anulado_a_completado_reactiva_y_descuenta_stock()
    {
        ['admin' => $admin, 'prod' => $prod, 'cliente' => $cliente] = $this->escenario(20);
        $venta = Venta::create([
            'idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id,
            'fecha' => now(), 'estado' => 'anulado', 'total' => 50,
        ]);
        $venta->detalles()->create([
            'idProducto' => $prod->idProducto, 'cantidad' => 5,
            'precio_unitario' => $prod->precio, 'subtotal' => 50,
        ]);

        $this->actingAs($admin)->put("/ventas/{$venta->idVenta}", [
            'idCliente' => $cliente->idCliente,
            'estado' => 'completado',
            'fecha' => now()->format('Y-m-d'),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 5]],
        ])->assertRedirect('/ventas');

        $this->assertEquals(15, $prod->fresh()->stock);
        $this->assertEquals('completado', $venta->fresh()->estado);
    }

    public function test_completado_a_completado_edita_cantidad_correctamente()
    {
        ['admin' => $admin, 'prod' => $prod, 'cliente' => $cliente] = $this->escenario(20);
        $venta = $this->crearVentaCompletado($admin, $cliente, $prod, 5);
        $this->assertEquals(15, $prod->fresh()->stock);

        // sube de 5 a 8 unidades
        $this->actingAs($admin)->put("/ventas/{$venta->idVenta}", [
            'idCliente' => $cliente->idCliente,
            'estado' => 'completado',
            'fecha' => now()->format('Y-m-d'),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 8]],
        ])->assertRedirect('/ventas');

        // 20 (devuelto) - 8 (nuevo) = 12
        $this->assertEquals(12, $prod->fresh()->stock);
    }

    public function test_anular_no_valida_stock_disponible()
    {
        // Si el stock actual del producto es menor a lo que tenía el venta original
        // (porque se vendió a alguien más), anular igual debe funcionar sin error de "stock insuficiente".
        ['admin' => $admin, 'prod' => $prod, 'cliente' => $cliente] = $this->escenario(20);
        $venta = $this->crearVentaCompletado($admin, $cliente, $prod, 5);
        $prod->lotes()->first()->update(['cantidad' => 0]); // se vendió todo lo demás a otro cliente
        $prod->update(['stock' => 0]);

        $this->actingAs($admin)->put("/ventas/{$venta->idVenta}", [
            'idCliente' => $cliente->idCliente,
            'estado' => 'anulado',
            'fecha' => now()->format('Y-m-d'),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 5]],
        ])->assertRedirect('/ventas');

        // Se devuelven las 5 unidades que sí tenía reservadas este venta: 0 + 5 = 5
        $this->assertEquals(5, $prod->fresh()->stock);
        $this->assertEquals('anulado', $venta->fresh()->estado);
    }
}
