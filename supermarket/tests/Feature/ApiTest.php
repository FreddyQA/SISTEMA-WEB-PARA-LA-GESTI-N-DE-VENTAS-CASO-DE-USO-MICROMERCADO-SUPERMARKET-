<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use App\Services\VentaService;
use App\Support\Sku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Cubre la API REST que consume la app móvil: login por token (sin
 * Sanctum — ver migración create_api_tokens_table), autorización por
 * rol, y que una venta hecha por este canal use exactamente la misma
 * lógica de stock que el admin web (App\Services\VentaService).
 */
class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function crearAdmin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('secret123'), 'rol' => 'administrador',
        ]);
    }

    protected function crearInvitado(): User
    {
        return User::create([
            'name' => 'Invitado', 'email' => 'invitado@test.com',
            'password' => bcrypt('secret123'), 'rol' => 'invitado',
        ]);
    }

    public function test_login_devuelve_token_valido_con_credenciales_correctas()
    {
        $this->crearAdmin();

        $response = $this->postJson('/api/login', ['email' => 'admin@test.com', 'password' => 'secret123']);

        $response->assertOk()->assertJsonStructure(['token', 'usuario' => ['id', 'nombre', 'email', 'rol']]);
        $this->assertDatabaseCount('api_tokens', 1);
    }

    public function test_login_rechaza_credenciales_incorrectas_sin_filtrar_el_token_hash()
    {
        $this->crearAdmin();

        $response = $this->postJson('/api/login', ['email' => 'admin@test.com', 'password' => 'incorrecta']);

        $response->assertStatus(401);
        $this->assertDatabaseCount('api_tokens', 0);
    }

    public function test_rutas_protegidas_rechazan_peticiones_sin_token()
    {
        $this->getJson('/api/productos')->assertStatus(401);
        $this->getJson('/api/dashboard')->assertStatus(401);
    }

    public function test_token_funciona_para_autenticar_y_logout_lo_invalida()
    {
        $this->crearAdmin();
        $token = $this->postJson('/api/login', ['email' => 'admin@test.com', 'password' => 'secret123'])->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')->assertOk()->assertJsonPath('data.email', 'admin@test.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')->assertStatus(401);
    }

    public function test_invitado_puede_ver_productos_y_registrar_venta_pero_no_ver_dashboard()
    {
        $invitado = $this->crearInvitado();
        $cat = Categoria::create(['nombre' => 'Bebidas']);
        $prod = Producto::create(['nombre' => 'Agua', 'precio' => 5, 'stock' => 10, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Juan Pérez', 'email' => 'juan@test.com']);
        $token = ApiToken::emitirPara($invitado);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/productos')->assertOk();

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/ventas', [
            'idCliente' => $cliente->idCliente,
            'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 3]],
        ]);
        $response->assertCreated();
        $this->assertEquals(15.0, $response->json('data.total'));
        $this->withHeader('Authorization', "******")->getJson('/api/ventas')->assertOk()->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('productos', ['idProducto' => $prod->idProducto, 'stock' => 6]);
    }

    public function test_venta_sin_stock_suficiente_devuelve_422_y_no_descuenta_nada()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'Bebidas']);
        $prod = Producto::create(['nombre' => 'Agua', 'precio' => 5, 'stock' => 2, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Juan Pérez', 'email' => 'juan2@test.com']);
        $token = ApiToken::emitirPara($admin);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/ventas', [
            'idCliente' => $cliente->idCliente,
            'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 5]],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('productos', ['idProducto' => $prod->idProducto, 'stock' => 2]);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_buscar_producto_por_codigo_sku()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'Bebidas']);
        $prod = Producto::create(['nombre' => 'Agua', 'precio' => 5, 'stock' => 10, 'idCategoria' => $cat->idCategoria]);
        $token = ApiToken::emitirPara($admin);

        $sku = Sku::format($prod->idProducto);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/productos/codigo/{$sku}")
            ->assertOk()->assertJsonPath('data.id', $prod->idProducto);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/productos/codigo/PRD-999999')
            ->assertStatus(404);
    }

    public function test_crear_cliente_desde_la_app_rechaza_correo_duplicado_activo()
    {
        $admin = $this->crearAdmin();
        Cliente::create(['nombre' => 'Existente', 'email' => 'dup@test.com']);
        $token = ApiToken::emitirPara($admin);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/clientes', [
            'nombre' => 'Otro Cliente', 'email' => 'dup@test.com',
        ]);

        $response->assertStatus(422);
    }

    // ---------- CATEGORÍAS ----------

    public function test_invitado_puede_listar_categorias_pero_no_crear()
    {
        $invitado = $this->crearInvitado();
        Categoria::create(['nombre' => 'Lácteos']);
        $token = ApiToken::emitirPara($invitado);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/categorias')->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/categorias', ['nombre' => 'Bebidas'])
            ->assertStatus(403);
    }

    public function test_admin_puede_crear_editar_y_eliminar_categoria()
    {
        $admin = $this->crearAdmin();
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $creada = $this->withHeaders($h)->postJson('/api/categorias', ['nombre' => 'Limpieza'])
            ->assertCreated()->json('data');

        $this->withHeaders($h)->putJson("/api/categorias/{$creada['id']}", ['nombre' => 'Limpieza del Hogar'])
            ->assertOk()->assertJsonPath('data.nombre', 'Limpieza del Hogar');

        $this->withHeaders($h)->deleteJson("/api/categorias/{$creada['id']}")->assertOk();
        $this->assertSoftDeleted('categorias', ['idCategoria' => $creada['id']]);
    }

    public function test_no_se_puede_eliminar_categoria_con_productos_por_api()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'Bebidas']);
        Producto::create(['nombre' => 'Agua', 'precio' => 5, 'stock' => 10, 'idCategoria' => $cat->idCategoria]);
        $token = ApiToken::emitirPara($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/categorias/{$cat->idCategoria}")
            ->assertStatus(422);
    }

    // ---------- REPORTES ----------

    public function test_invitado_no_puede_ver_reportes()
    {
        $invitado = $this->crearInvitado();
        $token = ApiToken::emitirPara($invitado);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/reportes/ventas')->assertStatus(403);
    }

    // ---------- PRODUCTOS: crear/editar ----------

    public function test_invitado_no_puede_crear_ni_editar_producto()
    {
        $invitado = $this->crearInvitado();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Fideo', 'precio' => 5, 'stock' => 10, 'idCategoria' => $cat->idCategoria]);
        $token = ApiToken::emitirPara($invitado);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/productos', ['nombre' => 'Arroz', 'precio' => 8, 'stock' => 5, 'idCategoria' => $cat->idCategoria])
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/productos/{$prod->idProducto}", ['nombre' => 'Fideo Grueso', 'precio' => 5, 'stock' => 10, 'idCategoria' => $cat->idCategoria])
            ->assertStatus(403);
    }

    public function test_admin_puede_crear_y_editar_producto()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'General']);
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $creado = $this->withHeaders($h)->postJson('/api/productos', [
            'nombre' => 'Aceite', 'precio' => 15.50, 'stock' => 20, 'idCategoria' => $cat->idCategoria,
        ])->assertCreated()->json('data');

        $this->withHeaders($h)->putJson("/api/productos/{$creado['id']}", [
            'nombre' => 'Aceite Vegetal 1L', 'precio' => 16, 'stock' => 18, 'idCategoria' => $cat->idCategoria,
        ])->assertOk()->assertJsonPath('data.nombre', 'Aceite Vegetal 1L');

        $this->assertDatabaseHas('productos', ['idProducto' => $creado['id'], 'stock' => 18]);
    }

    // ---------- VENTAS: editar/anular/eliminar ----------

    public function test_invitado_no_puede_editar_ni_eliminar_venta()
    {
        $invitado = $this->crearInvitado();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Pan', 'precio' => 2, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'edit@test.com']);
        $venta = app(VentaService::class)->crear([
            'idCliente' => $cliente->idCliente, 'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 2]],
        ], null);
        $token = ApiToken::emitirPara($invitado);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/ventas/{$venta->idVenta}", [
                'idCliente' => $cliente->idCliente, 'fecha' => now()->toDateString(), 'estado' => 'anulado',
                'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 2]],
            ])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/ventas/{$venta->idVenta}")->assertStatus(403);
    }

    public function test_admin_puede_anular_un_venta_y_el_stock_se_restituye()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Pan', 'precio' => 2, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'anular@test.com']);
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $venta = $this->withHeaders($h)->postJson('/api/ventas', [
            'idCliente' => $cliente->idCliente, 'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 5]],
        ])->json('data');
        $this->assertDatabaseHas('productos', ['idProducto' => $prod->idProducto, 'stock' => 45]);

        // El botón "Anular" de la app reenvía los mismos datos que ya
        // tenía el venta, solo con estado=anulado.
        $this->withHeaders($h)->putJson("/api/ventas/{$venta['id']}", [
            'idCliente' => $cliente->idCliente, 'fecha' => $venta['fecha'], 'estado' => 'anulado',
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 5]],
        ])->assertOk()->assertJsonPath('data.estado', 'anulado');

        $this->assertDatabaseHas('productos', ['idProducto' => $prod->idProducto, 'stock' => 50]);
    }

    public function test_admin_puede_eliminar_venta_y_restituye_stock()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Pan', 'precio' => 2, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'del@test.com']);
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $venta = $this->withHeaders($h)->postJson('/api/ventas', [
            'idCliente' => $cliente->idCliente, 'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 3]],
        ])->json('data');

        $this->withHeaders($h)->deleteJson("/api/ventas/{$venta['id']}")->assertOk();
        $this->assertSoftDeleted('ventas', ['idVenta' => $venta['id']]);
        $this->assertDatabaseHas('productos', ['idProducto' => $prod->idProducto, 'stock' => 50]);
    }

    // ---------- USUARIOS ----------

    public function test_invitado_no_puede_gestionar_usuarios()
    {
        $invitado = $this->crearInvitado();
        $token = ApiToken::emitirPara($invitado);

        $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/usuarios')->assertStatus(403);
    }

    public function test_admin_puede_crear_editar_y_eliminar_usuario()
    {
        $admin = $this->crearAdmin();
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $creado = $this->withHeaders($h)->postJson('/api/usuarios', [
            'name' => 'Empleado Nuevo', 'email' => 'nuevo@test.com',
            'password' => 'clave123', 'password_confirmation' => 'clave123', 'rol' => 'invitado',
        ])->assertCreated()->json('data');

        $this->withHeaders($h)->putJson("/api/usuarios/{$creado['id']}", [
            'name' => 'Empleado Editado', 'email' => 'nuevo@test.com', 'rol' => 'invitado',
        ])->assertOk()->assertJsonPath('data.nombre', 'Empleado Editado');

        $this->withHeaders($h)->deleteJson("/api/usuarios/{$creado['id']}")->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $creado['id']]);
    }

    public function test_admin_no_puede_eliminar_su_propio_usuario_ni_cambiarse_el_rol()
    {
        $admin = $this->crearAdmin();
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($h)->deleteJson("/api/usuarios/{$admin->id}")->assertStatus(422);

        $this->withHeaders($h)->putJson("/api/usuarios/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'rol' => 'invitado',
        ])->assertStatus(422);
    }

    // ---------- PRODUCTOS: imagen ----------

    public function test_admin_puede_subir_y_quitar_imagen_de_producto()
    {
        Storage::fake('public');
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Galleta', 'precio' => 3, 'stock' => 10, 'idCategoria' => $cat->idCategoria]);
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $resp = $this->withHeaders($h)->post("/api/productos/{$prod->idProducto}/imagen", [
            'imagen' => UploadedFile::fake()->image('galleta.jpg', 300, 300),
        ], ['Accept' => 'application/json']);

        $resp->assertOk();
        $this->assertNotNull($resp->json('data.imagen_url'));
        $ruta = $prod->fresh()->imagen;
        Storage::disk('public')->assertExists($ruta);

        $this->withHeaders($h)->deleteJson("/api/productos/{$prod->idProducto}/imagen")
            ->assertOk()->assertJsonPath('data.imagen_url', null);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_subir_imagen_rechaza_archivos_que_no_son_imagen_y_a_invitados()
    {
        Storage::fake('public');
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Galleta', 'precio' => 3, 'stock' => 10, 'idCategoria' => $cat->idCategoria]);

        $adminToken = ApiToken::emitirPara($this->crearAdmin());
        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->post("/api/productos/{$prod->idProducto}/imagen", [
                'imagen' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertStatus(422);

        $invToken = ApiToken::emitirPara($this->crearInvitado());
        $this->withHeader('Authorization', "Bearer {$invToken}")
            ->post("/api/productos/{$prod->idProducto}/imagen", [
                'imagen' => UploadedFile::fake()->image('a.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertStatus(403);
    }

    // ---------- CLIENTES: editar/eliminar ----------

    public function test_admin_puede_editar_cliente_y_se_rechaza_correo_de_otro()
    {
        $admin = $this->crearAdmin();
        $a = Cliente::create(['nombre' => 'Ana Lopez', 'email' => 'ana@test.com']);
        Cliente::create(['nombre' => 'Beto Rojas', 'email' => 'beto@test.com']);
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($h)->putJson("/api/clientes/{$a->idCliente}", [
            'nombre' => 'Ana Lopez Diaz', 'email' => 'ana@test.com', 'telefono' => '70012345',
        ])->assertOk()->assertJsonPath('data.nombre', 'Ana Lopez Diaz');

        $this->withHeaders($h)->putJson("/api/clientes/{$a->idCliente}", [
            'nombre' => 'Ana Lopez Diaz', 'email' => 'beto@test.com',
        ])->assertStatus(422);
    }

    public function test_invitado_no_puede_editar_ni_eliminar_cliente()
    {
        $c = Cliente::create(['nombre' => 'Ana Lopez', 'email' => 'ana2@test.com']);
        $token = ApiToken::emitirPara($this->crearInvitado());
        $h = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($h)->putJson("/api/clientes/{$c->idCliente}", ['nombre' => 'X Y', 'email' => 'x@test.com'])->assertStatus(403);
        $this->withHeaders($h)->deleteJson("/api/clientes/{$c->idCliente}")->assertStatus(403);
    }

    public function test_no_se_puede_eliminar_cliente_con_ventas_pero_si_sin_ventas()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Pan', 'precio' => 2, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $conVentas = Cliente::create(['nombre' => 'Con Ventas', 'email' => 'cp@test.com']);
        $sinVentas = Cliente::create(['nombre' => 'Sin Ventas', 'email' => 'sp@test.com']);
        app(VentaService::class)->crear([
            'idCliente' => $conVentas->idCliente, 'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $prod->idProducto, 'cantidad' => 1]],
        ], null);
        $token = ApiToken::emitirPara($admin);
        $h = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($h)->deleteJson("/api/clientes/{$conVentas->idCliente}")->assertStatus(422);
        $this->withHeaders($h)->deleteJson("/api/clientes/{$sinVentas->idCliente}")->assertOk();
        $this->assertSoftDeleted('clientes', ['idCliente' => $sinVentas->idCliente]);
    }

    public function test_reporte_de_ventas_excluye_ventas_anulados_de_las_metricas()
    {
        $admin = $this->crearAdmin();
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Pan', 'precio' => 10, 'stock' => 100, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Reporte', 'email' => 'rep@test.com']);

        Venta::create(['idCliente' => $cliente->idCliente, 'total' => 100, 'estado' => 'completado', 'fecha' => now()]);
        Venta::create(['idCliente' => $cliente->idCliente, 'total' => 50, 'estado' => 'anulado', 'fecha' => now()]);

        $token = ApiToken::emitirPara($admin);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/reportes/ventas');

        $response->assertOk();
        $this->assertEquals(100.0, $response->json('metricas.total_ventas'));
        $this->assertEquals(2, $response->json('metricas.cantidad_ventas'));
        $this->assertEquals(2, $response->json('metricas.cantidad_ventas'));
        $this->assertCount(2, $response->json('ventas.data'));
    }
}
