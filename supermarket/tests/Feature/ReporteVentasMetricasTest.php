<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteVentasMetricasTest extends TestCase
{
    use RefreshDatabase;

    public function test_unidades_vendidas_excluye_ventas_anulados()
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'rol' => 'administrador']);
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Producto Test', 'precio' => 10, 'stock' => 100, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente@test.com']);

        // Venta completada: 5 unidades -> SÍ debe contar
        $p1 = Venta::create(['idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id, 'fecha' => now(), 'estado' => 'completado', 'total' => 50]);
        $p1->detalles()->create(['idProducto' => $prod->idProducto, 'cantidad' => 5, 'precio_unitario' => 10, 'subtotal' => 50]);

        // Venta anulada: 7 unidades -> NO debe contar
        $p2 = Venta::create(['idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id, 'fecha' => now(), 'estado' => 'anulado', 'total' => 70]);
        $p2->detalles()->create(['idProducto' => $prod->idProducto, 'cantidad' => 7, 'precio_unitario' => 10, 'subtotal' => 70]);

        $resp = $this->actingAs($admin)->get('/reportes/ventas');
        $resp->assertOk();
        $resp->assertViewHas('totalUnidades', 5);
        $resp->assertViewHas('totalVentas', 50.0);
        $resp->assertViewHas('cantidadVentas', 2); // Incluye ambas transacciones, pero excluye las anuladas del importe.
    }
}
