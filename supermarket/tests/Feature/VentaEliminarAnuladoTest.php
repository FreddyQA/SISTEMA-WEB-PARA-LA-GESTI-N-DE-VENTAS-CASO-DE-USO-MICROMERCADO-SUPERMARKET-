<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaEliminarAnuladoTest extends TestCase
{
    use RefreshDatabase;

    protected function escenario(int $stockInicial = 10): array
    {
        $admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol'      => 'administrador',
        ]);

        $cat = Categoria::create(['nombre' => 'Bebidas']);

        $prod = Producto::create([
            'nombre'      => 'Gaseosa Test',
            'precio'      => 15.00,
            'stock'       => $stockInicial,
            'idCategoria' => $cat->idCategoria,
        ]);

        $cliente = Cliente::create([
            'nombre' => 'Juan Perez',
            'email'  => 'juan@test.com',
        ]);

        return compact('admin', 'cat', 'prod', 'cliente');
    }

    public function test_eliminar_venta_completado_restaura_stock()
    {
        ['admin' => $admin, 'prod' => $prod, 'cliente' => $cliente] = $this->escenario(10);

        // Crear venta completado de 3 unidades (stock pasa de 10 a 7)
        $venta = Venta::create([
            'idCliente' => $cliente->idCliente,
            'idUsuario' => $admin->id,
            'fecha'     => now(),
            'estado'    => 'completado',
            'total'     => 45.00,
        ]);
        $venta->detalles()->create([
            'idProducto'      => $prod->idProducto,
            'cantidad'        => 3,
            'precio_unitario' => 15.00,
            'subtotal'        => 45.00,
        ]);
        $prod->decrement('stock', 3);
        $this->assertEquals(7, $prod->fresh()->stock);

        // Eliminar venta completado -> debe devolver las 3 unidades al stock
        $this->actingAs($admin)->delete("/ventas/{$venta->idVenta}")
            ->assertRedirect('/ventas');

        $this->assertEquals(10, $prod->fresh()->stock);
        $this->assertNull(Venta::find($venta->idVenta));
    }

    public function test_eliminar_venta_anulado_no_crea_stock_fantasma()
    {
        ['admin' => $admin, 'prod' => $prod, 'cliente' => $cliente] = $this->escenario(10);

        // 1. Venta completado de 3 unidades -> stock pasa de 10 a 7
        $venta = Venta::create([
            'idCliente' => $cliente->idCliente,
            'idUsuario' => $admin->id,
            'fecha'     => now(),
            'estado'    => 'completado',
            'total'     => 45.00,
        ]);
        $venta->detalles()->create([
            'idProducto'      => $prod->idProducto,
            'cantidad'        => 3,
            'precio_unitario' => 15.00,
            'subtotal'        => 45.00,
        ]);
        $prod->decrement('stock', 3);
        $this->assertEquals(7, $prod->fresh()->stock);

        // 2. Se anula el venta a través del controlador -> stock se restituye a 10
        $this->actingAs($admin)->put("/ventas/{$venta->idVenta}", [
            'idCliente' => $cliente->idCliente,
            'estado'    => 'anulado',
            'fecha'     => now()->format('Y-m-d'),
            'items'     => [['idProducto' => $prod->idProducto, 'cantidad' => 3]],
        ]);
        $this->assertEquals(10, $prod->fresh()->stock);
        $this->assertEquals('anulado', $venta->fresh()->estado);

        // 3. Ahora se elimina el venta que ya está anulado
        // CRÍTICO: NO debe volver a sumar las 3 unidades (el stock debe permanecer en 10, NO 13)
        $this->actingAs($admin)->delete("/ventas/{$venta->idVenta}")
            ->assertRedirect('/ventas');

        $this->assertEquals(10, $prod->fresh()->stock, 'El stock aumentó indebidamente al eliminar un venta ya anulado.');
        $this->assertNull(Venta::find($venta->idVenta));
    }
}
