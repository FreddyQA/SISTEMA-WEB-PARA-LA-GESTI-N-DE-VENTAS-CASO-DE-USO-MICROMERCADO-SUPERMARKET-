<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Papelera única (/papelera) con una pestaña por tipo y acciones en lote.
 * Antes eran cuatro papeleras separadas (solo Productos tenía una navegable).
 */
class PapeleraGeneralTest extends TestCase
{
    use RefreshDatabase;

    protected function crearAdmin(): User
    {
        return User::create([
            'name' => 'Administrador', 'email' => 'admin@supermarket.test',
            'password' => bcrypt('secret123'), 'rol' => 'administrador',
        ]);
    }

    protected function crearInvitado(): User
    {
        return User::create([
            'name' => 'Invitado', 'email' => 'invitado@supermarket.test',
            'password' => bcrypt('secret123'), 'rol' => 'invitado',
        ]);
    }

    protected function crearVentaDe(Cliente $cliente, Producto $producto): Venta
    {
        $venta = Venta::create(['idCliente' => $cliente->idCliente, 'total' => 1, 'estado' => 'completado', 'fecha' => now()]);
        DetalleVenta::create([
            'idVenta' => $venta->idVenta, 'idProducto' => $producto->idProducto,
            'cantidad' => 1, 'precio_unitario' => 1, 'subtotal' => 1,
        ]);

        return $venta;
    }

    // ───────────── Vista y pestañas ─────────────

    public function test_la_papelera_muestra_conteos_de_todas_las_pestanas_y_la_de_productos_por_defecto()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'Lácteos']);
        Producto::create(['nombre' => 'Leche Entera', 'precio' => 8, 'stock' => 5, 'idCategoria' => $cat->idCategoria])->delete();
        Categoria::create(['nombre' => 'Panadería'])->delete();
        Cliente::create(['nombre' => 'Ana Lopez', 'email' => 'ana@test.com'])->delete();

        $response = $this->actingAs($admin)->get('/papelera');

        $response->assertOk()->assertSee('Leche Entera');
        $response->assertViewHas('tipo', 'productos');
        $response->assertViewHas('conteos', ['productos' => 1, 'categorias' => 1, 'clientes' => 1]);
        $response->assertViewHas('total', 3);
    }

    public function test_cada_pestana_lista_solo_su_tipo_y_un_tipo_invalido_cae_en_productos()
    {
        $admin = $this->crearAdmin();
        Categoria::create(['nombre' => 'Panadería'])->delete();
        $this->actingAs($admin)->get('/papelera?tipo=categorias')
            ->assertOk()->assertSee('Panadería');

        $this->actingAs($admin)->get('/papelera?tipo=cualquier-cosa')
            ->assertOk()->assertViewHas('tipo', 'productos');
    }

    public function test_pestana_vacia_muestra_estado_vacio()
    {
        $this->actingAs($this->crearAdmin())->get('/papelera?tipo=clientes')
            ->assertOk()->assertSee('No hay clientes en la papelera');
    }

    public function test_el_sidebar_muestra_el_contador_solo_cuando_hay_elementos_en_la_papelera()
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->get('/dashboard')->assertDontSee('<span class="link-badge"', false);

        Categoria::create(['nombre' => 'Panadería'])->delete();

        $this->actingAs($admin)->get('/dashboard')->assertSee('<span class="link-badge"', false);
    }

    // ───────────── Permisos ─────────────

    public function test_invitado_no_puede_ver_ni_operar_la_papelera()
    {
        $invitado = $this->crearInvitado();
        $cat = Categoria::create(['nombre' => 'Panadería']);
        $cat->delete();

        $this->actingAs($invitado)->get('/papelera')->assertForbidden();
        $this->actingAs($invitado)->post('/papelera/restaurar', ['tipo' => 'categorias', 'ids' => [$cat->idCategoria]])->assertForbidden();
        $this->actingAs($invitado)->post('/papelera/eliminar', ['tipo' => 'categorias', 'ids' => [$cat->idCategoria]])->assertForbidden();

        $this->assertSoftDeleted('categorias', ['idCategoria' => $cat->idCategoria]);
    }

    public function test_las_urls_de_las_antiguas_papeleras_redirigen_a_la_pestana_correcta()
    {
        $admin = $this->crearAdmin();

        foreach (['productos', 'categorias', 'clientes'] as $tipo) {
            $this->actingAs($admin)->get("/{$tipo}/papelera")
                ->assertRedirect(route('papelera.index', ['tipo' => $tipo]));
        }
    }

    // ───────────── Restaurar ─────────────

    public function test_restaurar_en_lote_devuelve_los_elementos_marcados()
    {
        $admin = $this->crearAdmin();
        $a = Cliente::create(['nombre' => 'Ana Lopez', 'email' => 'ana@test.com']);
        $b = Cliente::create(['nombre' => 'Beto Rojas', 'email' => 'beto@test.com']);
        $c = Cliente::create(['nombre' => 'Carla Paz', 'email' => 'carla@test.com']);
        foreach ([$a, $b, $c] as $cl) {
            $cl->delete();
        }

        $response = $this->actingAs($admin)->post('/papelera/restaurar', [
            'tipo' => 'clientes', 'ids' => [$a->idCliente, $b->idCliente],
        ]);

        $response->assertRedirect(route('papelera.index', ['tipo' => 'clientes']));
        $this->assertStringContainsString('2 clientes restaurados', session('success'));
        $this->assertDatabaseHas('clientes', ['idCliente' => $a->idCliente, 'deleted_at' => null]);
        $this->assertDatabaseHas('clientes', ['idCliente' => $b->idCliente, 'deleted_at' => null]);
        $this->assertSoftDeleted('clientes', ['idCliente' => $c->idCliente]);
    }

    public function test_mensaje_de_exito_respeta_el_genero_gramatical()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'Panadería']);
        $cat->delete();

        $this->actingAs($admin)->post('/papelera/restaurar', ['tipo' => 'categorias', 'ids' => [$cat->idCategoria]]);

        $this->assertSame('Categoría restaurada.', session('success'));
    }

    public function test_no_se_restaura_un_producto_mientras_su_categoria_siga_en_la_papelera()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'Lácteos']);
        $prod = Producto::create(['nombre' => 'Leche', 'precio' => 8, 'stock' => 5, 'idCategoria' => $cat->idCategoria]);
        $prod->delete();
        $cat->delete();

        $this->actingAs($admin)->post('/papelera/restaurar', ['tipo' => 'productos', 'ids' => [$prod->idProducto]]);

        $this->assertStringContainsString('Lácteos', session('error'));
        $this->assertSoftDeleted('productos', ['idProducto' => $prod->idProducto]);

        // Restaurando primero la categoría, el producto ya puede volver.
        $this->actingAs($admin)->post('/papelera/restaurar', ['tipo' => 'categorias', 'ids' => [$cat->idCategoria]]);
        $this->actingAs($admin)->post('/papelera/restaurar', ['tipo' => 'productos', 'ids' => [$prod->idProducto]]);

        $this->assertDatabaseHas('productos', ['idProducto' => $prod->idProducto, 'deleted_at' => null]);
    }

    // ───────────── Eliminar definitivamente ─────────────

    public function test_lote_mixto_elimina_lo_libre_y_omite_lo_que_tiene_historial_informando_el_motivo()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Pan', 'precio' => 1, 'stock' => 100, 'idCategoria' => $cat->idCategoria]);
        $conVentas = Cliente::create(['nombre' => 'Con Ventas', 'email' => 'cp@test.com']);
        $libre = Cliente::create(['nombre' => 'Cliente Libre', 'email' => 'libre@test.com']);
        $venta = $this->crearVentaDe($conVentas, $prod);
        $venta->delete();       // el venta en papelera igual cuenta como dependencia
        $conVentas->delete();
        $libre->delete();

        $response = $this->actingAs($admin)->post('/papelera/eliminar', [
            'tipo' => 'clientes', 'ids' => [$conVentas->idCliente, $libre->idCliente],
        ]);

        $response->assertRedirect(route('papelera.index', ['tipo' => 'clientes']));
        $this->assertDatabaseMissing('clientes', ['idCliente' => $libre->idCliente]);
        $this->assertSoftDeleted('clientes', ['idCliente' => $conVentas->idCliente]);
        $this->assertSame('Cliente eliminado de forma permanente.', session('success'));
        $this->assertStringContainsString('«Con Ventas»', session('error'));
        $this->assertStringContainsString('venta', session('error'));
    }

    public function test_categoria_con_productos_en_papelera_no_se_elimina_definitivamente()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'Bebidas']);
        Producto::create(['nombre' => 'Agua 2L', 'precio' => 8, 'stock' => 10, 'idCategoria' => $cat->idCategoria])->delete();
        $cat->delete();

        $this->actingAs($admin)->post('/papelera/eliminar', ['tipo' => 'categorias', 'ids' => [$cat->idCategoria]]);

        $this->assertNotNull(session('error'));
        $this->assertSoftDeleted('categorias', ['idCategoria' => $cat->idCategoria]);
    }

    public function test_producto_con_ventas_no_se_elimina_pero_uno_sin_ventas_si_y_se_borra_su_imagen()
    {
        Storage::fake('public');
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'General']);
        $vendido = Producto::create(['nombre' => 'Arroz', 'precio' => 5, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $this->crearVentaDe(Cliente::create(['nombre' => 'Cliente X', 'email' => 'x@test.com']), $vendido);

        Storage::disk('public')->put('productos/galleta.jpg', 'fake');
        $sinVentas = Producto::create(['nombre' => 'Galleta', 'precio' => 3, 'stock' => 9, 'idCategoria' => $cat->idCategoria, 'imagen' => 'productos/galleta.jpg']);
        $vendido->delete();
        $sinVentas->delete();

        $this->actingAs($admin)->post('/papelera/eliminar', [
            'tipo' => 'productos', 'ids' => [$vendido->idProducto, $sinVentas->idProducto],
        ]);

        $this->assertSoftDeleted('productos', ['idProducto' => $vendido->idProducto]);
        $this->assertDatabaseMissing('productos', ['idProducto' => $sinVentas->idProducto]);
        Storage::disk('public')->assertMissing('productos/galleta.jpg');
    }

    // ───────────── Validación ─────────────

    public function test_las_acciones_exigen_tipo_valido_e_ids()
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->post('/papelera/restaurar', ['tipo' => 'productos'])
            ->assertSessionHasErrors('ids');
        $this->actingAs($admin)->post('/papelera/eliminar', ['tipo' => 'inventado', 'ids' => [1]])
            ->assertSessionHasErrors('tipo');
    }

    // ───────────── Restricciones de base de datos (defensa en profundidad) ─────────────

    public function test_no_se_permite_repetir_producto_en_detalle_ventas_a_nivel_de_bd()
    {
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Arroz', 'precio' => 5, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'test@test.com']);
        $venta = Venta::create(['idCliente' => $cliente->idCliente, 'total' => 10, 'estado' => 'completado', 'fecha' => now()]);

        DetalleVenta::create(['idVenta' => $venta->idVenta, 'idProducto' => $prod->idProducto, 'cantidad' => 1, 'precio_unitario' => 5, 'subtotal' => 5]);

        $this->expectException(QueryException::class);
        DetalleVenta::create(['idVenta' => $venta->idVenta, 'idProducto' => $prod->idProducto, 'cantidad' => 2, 'precio_unitario' => 5, 'subtotal' => 10]);
    }
}
