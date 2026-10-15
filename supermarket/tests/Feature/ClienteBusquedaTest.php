<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteBusquedaTest extends TestCase
{
    use RefreshDatabase;

    public function test_busqueda_filtra_por_nombre_o_email()
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'rol' => 'administrador']);
        Cliente::create(['nombre' => 'María Pérez', 'email' => 'maria@test.com']);
        Cliente::create(['nombre' => 'Juan Gómez', 'email' => 'juan@test.com']);

        $resp = $this->actingAs($admin)->get('/clientes?q=maria');
        $resp->assertOk();
        $this->assertEquals(1, $resp->viewData('clientes')->total());
        $this->assertEquals('María Pérez', $resp->viewData('clientes')->first()->nombre);

        // Búsqueda por email también debe encontrar coincidencias
        $resp2 = $this->actingAs($admin)->get('/clientes?q=juan@test.com');
        $this->assertEquals(1, $resp2->viewData('clientes')->total());

        // Sin filtro, deben aparecer todos
        $resp3 = $this->actingAs($admin)->get('/clientes');
        $this->assertEquals(2, $resp3->viewData('clientes')->total());
    }
}
