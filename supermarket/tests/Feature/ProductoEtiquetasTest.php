<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoEtiquetasTest extends TestCase
{
    use RefreshDatabase;

    public function test_etiqueta_de_producto_renderiza_y_la_imagen_qr_se_genera_en_el_servidor(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-etiqueta@test.com',
            'password' => bcrypt('secret'),
            'rol' => 'administrador',
        ]);
        $categoria = Categoria::create(['nombre' => 'General']);
        $producto = Producto::create([
            'nombre' => 'Producto QR',
            'precio' => 12.5,
            'stock' => 3,
            'idCategoria' => $categoria->idCategoria,
        ]);

        $this->actingAs($admin)
            ->get('/productos')
            ->assertOk()
            ->assertSee('btn-label-window');

        $respuesta = $this->actingAs($admin)->get(route('productos.qr.image', $producto->idProducto));

        $respuesta->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($respuesta->getContent(), 0, 8));
    }
}
