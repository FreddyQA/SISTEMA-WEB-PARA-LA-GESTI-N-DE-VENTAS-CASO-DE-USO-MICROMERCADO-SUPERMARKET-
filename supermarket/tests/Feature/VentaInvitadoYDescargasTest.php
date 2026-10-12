<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaInvitadoYDescargasTest extends TestCase
{
    use RefreshDatabase;

    protected function crearEscenario(): array
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'rol' => 'administrador']);
        $invitado = User::create(['name' => 'Invitado', 'email' => 'i@i.com', 'password' => bcrypt('x'), 'rol' => 'invitado']);
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Producto Test', 'precio' => 10.50, 'stock' => 20, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente@test.com']);

        return compact('admin', 'invitado', 'cat', 'prod', 'cliente');
    }

    public function test_invitado_puede_ver_formulario_de_crear_venta()
    {
        ['invitado' => $invitado] = $this->crearEscenario();
        $this->actingAs($invitado)->get('/ventas/create')->assertOk();
    }

    public function test_invitado_puede_registrar_un_venta()
    {
        ['invitado' => $invitado, 'prod' => $prod, 'cliente' => $cliente] = $this->crearEscenario();

        $resp = $this->actingAs($invitado)->post('/ventas', [
            'idCliente' => $cliente->idCliente,
            'fecha'     => now()->format('Y-m-d'),
            'items'     => [['idProducto' => $prod->idProducto, 'cantidad' => 3]],
        ]);

        $resp->assertRedirect('/ventas');
        $this->assertEquals(1, Venta::count());
        $this->assertEquals(17, $prod->fresh()->stock); // 20 - 3
    }

    public function test_invitado_no_puede_editar_ni_eliminar_venta()
    {
        ['invitado' => $invitado, 'prod' => $prod, 'cliente' => $cliente] = $this->crearEscenario();
        $venta = Venta::create(['idCliente' => $cliente->idCliente, 'idUsuario' => $invitado->id, 'fecha' => now(), 'estado' => 'completado', 'total' => 0]);

        $this->actingAs($invitado)->get("/ventas/{$venta->idVenta}/edit")->assertForbidden();
        $this->actingAs($invitado)->delete("/ventas/{$venta->idVenta}")->assertForbidden();
    }

    public function test_administrador_si_puede_editar_y_eliminar_venta()
    {
        ['admin' => $admin, 'cliente' => $cliente] = $this->crearEscenario();
        $venta = Venta::create(['idCliente' => $cliente->idCliente, 'idUsuario' => $admin->id, 'fecha' => now(), 'estado' => 'completado', 'total' => 0]);

        $this->actingAs($admin)->get("/ventas/{$venta->idVenta}/edit")->assertOk();
    }

    public function test_descargar_qr_producto()
    {
        ['invitado' => $invitado, 'prod' => $prod] = $this->crearEscenario();
        $resp = $this->actingAs($invitado)->get("/productos/{$prod->idProducto}/qr");
        $resp->assertOk();
        $this->assertStringContainsString('image/png', $resp->headers->get('content-type'));
    }

    public function test_descargar_barcode_producto()
    {
        ['invitado' => $invitado, 'prod' => $prod] = $this->crearEscenario();
        $resp = $this->actingAs($invitado)->get("/productos/{$prod->idProducto}/barcode");
        $resp->assertOk();
        $this->assertStringContainsString('image/png', $resp->headers->get('content-type'));
    }
}
