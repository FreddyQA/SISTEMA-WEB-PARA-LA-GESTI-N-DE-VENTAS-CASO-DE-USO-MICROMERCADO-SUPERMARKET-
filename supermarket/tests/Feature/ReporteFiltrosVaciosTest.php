<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteFiltrosVaciosTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporte_ventas_con_cero_registros_retorna_200()
    {
        $admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol'      => 'administrador',
        ]);

        $response = $this->actingAs($admin)->get('/reportes/ventas');

        $response->assertOk();
        $response->assertViewHas('cantidadVentas', 0);
        $response->assertViewHas('totalVentas', 0.0);
        $response->assertViewHas('totalUnidades', 0);
        $response->assertSee('No hay ventas registradas en el período seleccionado');
    }

    public function test_filtro_fechas_sin_coincidencias_retorna_200()
    {
        $admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol'      => 'administrador',
        ]);

        $response = $this->actingAs($admin)->get('/reportes/ventas?fecha_inicio=2000-01-01&fecha_fin=2000-01-02');

        $response->assertOk();
        $response->assertViewHas('cantidadVentas', 0);
        $response->assertViewHas('totalVentas', 0.0);
        $response->assertSee('No hay ventas registradas en el período seleccionado');
    }
}
