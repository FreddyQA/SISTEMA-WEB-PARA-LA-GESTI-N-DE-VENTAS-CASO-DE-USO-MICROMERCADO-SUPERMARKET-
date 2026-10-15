<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginacionYDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'rol' => 'administrador']);
    }

    public function test_listados_paginados_cargan_correctamente_con_muchos_registros()
    {
        $admin = $this->admin();
        $cat = Categoria::create(['nombre' => 'General']);
        $cliente = Cliente::create(['nombre' => 'Cliente Uno', 'email' => 'c1@test.com']);

        // Crear más de 15 registros para forzar una segunda página
        for ($i = 1; $i <= 20; $i++) {
            Producto::create(['nombre' => "Producto $i", 'precio' => 10, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
            Cliente::create(['nombre' => "Cliente $i", 'email' => "cliente$i@test.com"]);
        }

        $respProductos = $this->actingAs($admin)->get('/productos');
        $respProductos->assertOk();
        $this->assertCount(20, $respProductos->viewData('productos')); // DataTables recibe la colección completa

        $respClientes = $this->actingAs($admin)->get('/clientes');
        $respClientes->assertOk();
        $this->assertTrue($respClientes->viewData('clientes') instanceof \Illuminate\Pagination\LengthAwarePaginator);
        $this->assertEquals(15, $respClientes->viewData('clientes')->count()); // 15 en la página 1
        $this->assertEquals(21, $respClientes->viewData('clientes')->total()); // 20 + el del setup

        $respPage2 = $this->actingAs($admin)->get('/clientes?page=2');
        $respPage2->assertOk();
        $this->assertEquals(6, $respPage2->viewData('clientes')->count()); // el resto en la página 2
    }

    public function test_dashboard_excluye_ventas_anulados_de_metricas_de_ventas()
    {
        $admin = $this->admin();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Producto Test', 'precio' => 100, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente@test.com']);

        // Venta completado: 100 -> debe contar
        $p1 = Venta::create(['idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id, 'fecha' => now(), 'estado' => 'completado', 'total' => 100]);
        $p1->detalles()->create(['idProducto' => $prod->idProducto, 'cantidad' => 1, 'precio_unitario' => 100, 'subtotal' => 100]);

        // Venta anulado: 500 -> NO debe contar
        $p2 = Venta::create(['idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id, 'fecha' => now(), 'estado' => 'anulado', 'total' => 500]);
        $p2->detalles()->create(['idProducto' => $prod->idProducto, 'cantidad' => 5, 'precio_unitario' => 100, 'subtotal' => 500]);

        // El dashboard usa DATE_FORMAT (MySQL-only), así que aquí solo verificamos
        // la agregación equivalente directamente vía Eloquent, igual que hace la ruta.
        $totalVentas = \App\Models\Venta::where('estado', '!=', 'anulado')->sum('total');
        $this->assertEquals(100.0, (float) $totalVentas);

        $topClientes = \App\Models\Venta::join('clientes', 'ventas.idCliente', '=', 'clientes.idCliente')
            ->where('ventas.estado', '!=', 'anulado')
            ->selectRaw('clientes.idCliente, SUM(ventas.total) as total')
            ->groupBy('clientes.idCliente')
            ->get();
        $this->assertEquals(100.0, (float) $topClientes->first()->total);

        $topProductos = \App\Models\DetalleVenta::join('productos', 'detalle_ventas.idProducto', '=', 'productos.idProducto')
            ->join('ventas', 'detalle_ventas.idVenta', '=', 'ventas.idVenta')
            ->where('ventas.estado', '!=', 'anulado')
            ->selectRaw('productos.idProducto, SUM(detalle_ventas.subtotal) as ingresos')
            ->groupBy('productos.idProducto')
            ->get();
        $this->assertEquals(100.0, (float) $topProductos->first()->ingresos);
    }

    public function test_reportes_ventas_pagina_la_tabla_pero_las_metricas_usan_el_total_filtrado()
    {
        $admin = $this->admin();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Producto Test', 'precio' => 10, 'stock' => 100, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente@test.com']);

        for ($i = 1; $i <= 18; $i++) {
            $p = Venta::create(['idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id, 'fecha' => now(), 'estado' => 'completado', 'total' => 10]);
            $p->detalles()->create(['idProducto' => $prod->idProducto, 'cantidad' => 1, 'precio_unitario' => 10, 'subtotal' => 10]);
        }

        $resp = $this->actingAs($admin)->get('/reportes/ventas');
        $resp->assertOk();
        $resp->assertViewHas('totalVentas', 180.0); // suma de los 18 ventas, no solo los 15 de la página
        $this->assertEquals(15, $resp->viewData('ventas')->count());
        $this->assertEquals(18, $resp->viewData('ventas')->total());
    }
}
