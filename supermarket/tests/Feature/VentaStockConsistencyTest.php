<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaStockConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function administrador(): User
    {
        return User::factory()->create(['rol' => 'administrador']);
    }

    private function producto(int $stock = 10, float $precio = 25.00): Producto
    {
        $categoria = Categoria::create(['nombre' => 'Categoria Test '.uniqid()]);

        return Producto::create([
            'nombre'      => 'Producto Test',
            'precio'      => $precio,
            'stock'       => $stock,
            'idCategoria' => $categoria->idCategoria,
        ]);
    }

    public function test_registrar_venta_descuenta_stock_y_calcula_el_total(): void
    {
        $producto = $this->producto(stock: 10, precio: 25.00);
        $cliente  = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente-test@example.com']);

        $response = $this->actingAs($this->administrador())->post(route('ventas.store'), [
            'idCliente' => $cliente->idCliente,
            'fecha'     => now()->toDateString(),
            'items'     => [
                ['idProducto' => $producto->idProducto, 'cantidad' => 3],
            ],
        ]);

        $response->assertRedirect(route('ventas.index'));

        $producto->refresh();
        $this->assertSame(7, $producto->stock); // 10 - 3

        $venta = Venta::first();
        $this->assertNotNull($venta);
        $this->assertEquals('75.00', $venta->total); // 3 * 25.00
        $this->assertCount(1, $venta->detalles);
    }

    public function test_no_se_puede_pedir_mas_stock_del_disponible(): void
    {
        $producto = $this->producto(stock: 2, precio: 10.00);
        $cliente  = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente-test2@example.com']);

        $response = $this->actingAs($this->administrador())->post(route('ventas.store'), [
            'idCliente' => $cliente->idCliente,
            'fecha'     => now()->toDateString(),
            'items'     => [
                ['idProducto' => $producto->idProducto, 'cantidad' => 5], // pide más de lo que hay
            ],
        ]);

        $response->assertSessionHasErrors('items');

        // El stock no debe haberse tocado y no debe haberse creado ningún venta:
        // la transacción completa se revierte, no solo la línea que falló.
        $producto->refresh();
        $this->assertSame(2, $producto->stock);
        $this->assertSame(0, Venta::count());
    }

    public function test_eliminar_un_venta_restituye_el_stock(): void
    {
        $producto = $this->producto(stock: 10, precio: 25.00);
        $cliente  = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente-test3@example.com']);
        $admin    = $this->administrador();

        $this->actingAs($admin)->post(route('ventas.store'), [
            'idCliente' => $cliente->idCliente,
            'fecha'     => now()->toDateString(),
            'items'     => [
                ['idProducto' => $producto->idProducto, 'cantidad' => 4],
            ],
        ]);

        $producto->refresh();
        $this->assertSame(6, $producto->stock);

        $venta = Venta::first();
        $this->actingAs($admin)->delete(route('ventas.destroy', $venta));

        $producto->refresh();
        $this->assertSame(10, $producto->stock); // vuelve al stock original
        $this->assertSoftDeleted('ventas', ['idVenta' => $venta->idVenta]);
    }
}
