<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_abrir_crear_editar_y_actualizar_producto_con_lote(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-productos@test.com',
            'password' => bcrypt('secret'),
            'rol' => 'administrador',
        ]);
        $categoria = Categoria::create(['nombre' => 'Alimentos']);

        $this->actingAs($admin)->get('/productos/create')->assertOk()
            ->assertSee('producto_fecha_vencimiento');

        $this->actingAs($admin)->post('/productos', [
            'nombre' => 'Arroz',
            'precio' => '12.50',
            'stock' => 10,
            'idCategoria' => $categoria->idCategoria,
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
        ])->assertRedirect('/productos');

        $producto = Producto::where('nombre', 'Arroz')->firstOrFail();
        $this->assertSame(10, $producto->stock);
        $this->assertTrue($producto->lotes()
            ->where('cantidad', 10)
            ->whereDate('fecha_vencimiento', now()->addMonths(6)->toDateString())
            ->exists());

        $this->actingAs($admin)->get("/productos/{$producto->idProducto}/edit")->assertOk()
            ->assertSee('producto_fecha_vencimiento');

        $this->actingAs($admin)->put("/productos/{$producto->idProducto}", [
            'nombre' => 'Arroz',
            'precio' => '13.00',
            'stock' => 15,
            'idCategoria' => $categoria->idCategoria,
            'fecha_vencimiento' => now()->addMonths(8)->toDateString(),
        ])->assertRedirect('/productos');

        $this->assertSame(15, $producto->fresh()->stock);
        $this->assertSame(10, $producto->lotes()
            ->whereDate('fecha_vencimiento', now()->addMonths(6)->toDateString())
            ->value('cantidad'));
        $this->assertSame(5, $producto->lotes()
            ->whereDate('fecha_vencimiento', now()->addMonths(8)->toDateString())
            ->value('cantidad'));
    }
}
