<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuscadoresYDetallesReporteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $invitado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrador Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'administrador',
        ]);

        $this->invitado = User::create([
            'name' => 'Invitado Test',
            'email' => 'invitado@test.com',
            'password' => bcrypt('password123'),
            'rol' => 'invitado',
        ]);
    }

    public function test_ruta_de_proveedores_fue_eliminada()
    {
        $res = $this->actingAs($this->admin)->get('/proveedores');
        $res->assertNotFound();
    }

    public function test_clientes_index_renderiza_datatable_en_tiempo_real_sin_formulario_estatico()
    {
        Cliente::create(['nombre' => 'Carlos Morales', 'email' => 'carlos@test.com']);

        $res = $this->actingAs($this->admin)->get('/clientes');
        $res->assertOk();
        $res->assertSee('id="tablaClientes"', false);
        $res->assertSee('Buscar cliente en tiempo real...', false);
        $res->assertDontSee('<form method="GET" action="'.route('clientes.index').'"', false);
    }

    public function test_ventas_index_renderiza_datatable_con_buscador_en_tiempo_real()
    {
        $cat = Categoria::create(['nombre' => 'Lácteos']);
        $prod = Producto::create(['nombre' => 'Leche Entera 1L', 'precio' => 6.50, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $cli = Cliente::create(['nombre' => 'Ana Ríos', 'email' => 'ana@test.com']);

        $venta = Venta::create([
            'idCliente' => $cli->idCliente,
            'idUsuario' => $this->admin->id,
            'fecha' => now()->toDateString(),
            'estado' => 'completado',
            'total' => 13.00,
        ]);

        DetalleVenta::create([
            'idVenta' => $venta->idVenta,
            'idProducto' => $prod->idProducto,
            'cantidad' => 2,
            'precio_unitario' => 6.50,
            'subtotal' => 13.00,
        ]);

        $res = $this->actingAs($this->admin)->get('/ventas');
        $res->assertOk();
        $res->assertSee('id="tablaVentas"', false);
        $res->assertSee('Buscar venta o producto en tiempo real...', false);
        $res->assertSee('Leche Entera 1L');
    }

    public function test_reportes_ventas_renderiza_detalles_enriquecidos_de_productos_y_buscador()
    {
        $cat = Categoria::create(['nombre' => 'Bebidas']);
        $prod = Producto::create(['nombre' => 'Jugo de Naranja 500ml', 'precio' => 5.00, 'stock' => 30, 'idCategoria' => $cat->idCategoria]);
        $cli = Cliente::create(['nombre' => 'Roberto Gómez', 'email' => 'roberto@test.com']);

        $venta = Venta::create([
            'idCliente' => $cli->idCliente,
            'idUsuario' => $this->admin->id,
            'fecha' => now()->toDateString(),
            'estado' => 'completado',
            'total' => 15.00,
        ]);

        DetalleVenta::create([
            'idVenta' => $venta->idVenta,
            'idProducto' => $prod->idProducto,
            'cantidad' => 3,
            'precio_unitario' => 5.00,
            'subtotal' => 15.00,
        ]);

        $res = $this->actingAs($this->admin)->get('/reportes/ventas');
        $res->assertOk();
        $res->assertSee('id="tablaReporte"', false);
        $res->assertSee('report-product-card', false);
        $res->assertSee('c/u: Bs 5.00', false);
        $res->assertSee('Jugo de Naranja 500ml', false);
        $res->assertSee('Bebidas', false);
        $res->assertSee('report-product-summary', false);
        $res->assertSee('Filtrar por cliente, producto o fecha en tiempo real...', false);
    }
}
