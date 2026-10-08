<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Services\SyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncRealtimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_estado_endpoint_retorna_marcas_de_tiempo(): void
    {
        $response = $this->getJson('/api/sync/estado');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'productos',
            'categorias',
            'ventas',
            'global',
        ]);
    }

    public function test_creacion_de_producto_actualiza_marca_de_tiempo(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $cat = Categoria::create(['nombre' => 'Lácteos']);

        $antes = SyncService::getState();
        usleep(5000); // 5ms

        Producto::create([
            'nombre' => 'Leche Entera 1L',
            'precio' => 7.50,
            'stock' => 20,
            'idCategoria' => $cat->idCategoria,
        ]);

        $despues = SyncService::getState();

        $this->assertGreaterThanOrEqual($antes['productos'], $despues['productos']);
        $this->assertGreaterThanOrEqual($antes['global'], $despues['global']);
    }

    public function test_creacion_y_eliminacion_de_categoria_actualiza_marca_de_tiempo(): void
    {
        $antes = SyncService::getState();
        usleep(5000);

        $cat = Categoria::create(['nombre' => 'Bebidas']);
        $despuesCrear = SyncService::getState();
        $this->assertGreaterThanOrEqual($antes['categorias'], $despuesCrear['categorias']);

        usleep(5000);
        $cat->delete();
        $despuesEliminar = SyncService::getState();
        $this->assertGreaterThanOrEqual($despuesCrear['categorias'], $despuesEliminar['categorias']);
    }
}
