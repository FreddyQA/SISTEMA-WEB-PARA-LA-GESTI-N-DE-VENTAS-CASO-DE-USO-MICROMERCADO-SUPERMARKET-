<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaRenumeracionYFechaTest extends TestCase
{
    use RefreshDatabase;

    public function test_ventas_se_renumeran_al_eliminar_uno_intermedio(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $cat = Categoria::create(['nombre' => 'General']);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'c@test.com']);
        $p1 = Producto::create(['nombre' => 'P1', 'precio' => 10, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);
        $p2 = Producto::create(['nombre' => 'P2', 'precio' => 20, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);

        // Crear 3 ventas consecutivos
        $ped1 = $this->actingAs($admin)->post(route('ventas.store'), [
            'idCliente' => $cliente->idCliente,
            'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $p1->idProducto, 'cantidad' => 1]],
        ]);
        $ped1->assertRedirect();

        $ped2 = $this->actingAs($admin)->post(route('ventas.store'), [
            'idCliente' => $cliente->idCliente,
            'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $p2->idProducto, 'cantidad' => 1]],
        ]);
        $ped2->assertRedirect();

        $ped3 = $this->actingAs($admin)->post(route('ventas.store'), [
            'idCliente' => $cliente->idCliente,
            'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $p1->idProducto, 'cantidad' => 1]],
        ]);
        $ped3->assertRedirect();

        $this->assertEquals([1, 2, 3], Venta::orderBy('idVenta')->pluck('numero')->toArray());

        // Eliminar el venta del medio (#2)
        $ventaMedio = Venta::where('numero', 2)->first();
        $this->actingAs($admin)->delete(route('ventas.destroy', $ventaMedio->idVenta));

        // Verificar que los restantes se renumeraron automáticamente sin hueco: 1 y 2
        $numerosRestantes = Venta::orderBy('idVenta')->pluck('numero')->toArray();
        $this->assertEquals([1, 2], $numerosRestantes);

        // Crear un nuevo venta (#3) y verificar que continúa la secuencia
        $this->actingAs($admin)->post(route('ventas.store'), [
            'idCliente' => $cliente->idCliente,
            'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $p1->idProducto, 'cantidad' => 1]],
        ]);

        $numerosActualizados = Venta::orderBy('idVenta')->pluck('numero')->toArray();
        $this->assertEquals([1, 2, 3], $numerosActualizados);
    }

    public function test_fecha_enviada_con_desfase_utc_de_manana_se_normaliza_a_hoy(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $cat = Categoria::create(['nombre' => 'General']);
        $cliente = Cliente::create(['nombre' => 'Cliente Test 2', 'email' => 'c2@test.com']);
        $p1 = Producto::create(['nombre' => 'P1', 'precio' => 10, 'stock' => 50, 'idCategoria' => $cat->idCategoria]);

        // Simular fecha enviada por cliente móvil usando toISOString() en UTC (mañana)
        $fechaManana = now()->addDay()->toDateString();

        $response = $this->actingAs($admin)->post(route('ventas.store'), [
            'idCliente' => $cliente->idCliente,
            'fecha' => $fechaManana,
            'items' => [['idProducto' => $p1->idProducto, 'cantidad' => 1]],
        ]);

        // No debe fallar con error de validación 'La fecha no puede ser futura'
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('ventas.index'));

        // El venta debe guardarse con la fecha de hoy
        $venta = Venta::latest('idVenta')->first();
        $this->assertNotNull($venta);
        $this->assertEquals(now()->toDateString(), $venta->fecha->format('Y-m-d'));
    }
}
