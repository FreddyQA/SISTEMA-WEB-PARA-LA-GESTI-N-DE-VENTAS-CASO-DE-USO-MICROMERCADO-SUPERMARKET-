<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function administrador(): User
    {
        return User::factory()->create(['rol' => 'administrador']);
    }

    public function test_no_se_puede_eliminar_una_categoria_con_productos_asociados(): void
    {
        $categoria = Categoria::create(['nombre' => 'Electrónica']);
        Producto::create([
            'nombre'      => 'Mouse',
            'precio'      => 50,
            'stock'       => 5,
            'idCategoria' => $categoria->idCategoria,
        ]);

        $response = $this->actingAs($this->administrador())
            ->delete(route('categorias.destroy', $categoria));

        $response->assertRedirect(route('categorias.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('categorias', ['idCategoria' => $categoria->idCategoria, 'deleted_at' => null]);
    }

    public function test_no_se_permiten_dos_categorias_activas_con_el_mismo_nombre(): void
    {
        Categoria::create(['nombre' => 'Limpieza']);

        $response = $this->actingAs($this->administrador())
            ->post(route('categorias.store'), ['nombre' => 'Limpieza']);

        $response->assertSessionHasErrors('nombre');
        $this->assertSame(1, Categoria::where('nombre', 'Limpieza')->count());
    }
}
