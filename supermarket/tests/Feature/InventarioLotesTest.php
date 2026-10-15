<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventarioLotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_venta_consumes_primero_los_lotes_vigentes_mas_proximos_a_vencer_y_anulacion_los_restituye(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-lotes@test.com',
            'password' => bcrypt('secret'),
            'rol' => 'administrador',
        ]);
        $categoria = Categoria::create(['nombre' => 'Alimentos']);
        $cliente = Cliente::create(['nombre' => 'Cliente', 'email' => 'cliente-lotes@test.com']);
        $producto = Producto::create([
            'nombre' => 'Leche',
            'precio' => 5,
            'stock' => 10,
            'idCategoria' => $categoria->idCategoria,
        ]);

        $vencido = $producto->lotes()->create([
            'cantidad' => 2,
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);
        $proximo = $producto->lotes()->create([
            'cantidad' => 3,
            'fecha_vencimiento' => now()->addDays(2)->toDateString(),
        ]);
        $sinVencimiento = $producto->lotes()->create([
            'cantidad' => 5,
            'fecha_vencimiento' => null,
        ]);

        $this->assertSame($vencido->idLote, $producto->fresh()->lotes->first()->idLote);

        $venta = app(VentaService::class)->crear([
            'idCliente' => $cliente->idCliente,
            'fecha' => now()->toDateString(),
            'items' => [['idProducto' => $producto->idProducto, 'cantidad' => 4]],
        ], $admin->id);

        $this->assertSame(6, $producto->fresh()->stock);
        $this->assertSame(2, $vencido->fresh()->cantidad);
        $this->assertSame(0, $proximo->fresh()->cantidad);
        $this->assertSame(4, $sinVencimiento->fresh()->cantidad);
        $this->assertSame(
            [
                ['idLote' => $proximo->idLote, 'cantidad' => 3],
                ['idLote' => $sinVencimiento->idLote, 'cantidad' => 1],
            ],
            DB::table('detalle_venta_lotes')
                ->join('detalle_ventas', 'detalle_venta_lotes.idDetalle', '=', 'detalle_ventas.idDetalle')
                ->where('detalle_ventas.idVenta', $venta->idVenta)
                ->orderBy('detalle_venta_lotes.id')
                ->get([
                    'detalle_venta_lotes.idLote',
                    'detalle_venta_lotes.cantidad',
                ])
                ->map(fn ($asignacion) => ['idLote' => $asignacion->idLote, 'cantidad' => $asignacion->cantidad])
                ->all()
        );

        app(VentaService::class)->actualizar($venta->idVenta, [
            'idCliente' => $cliente->idCliente,
            'fecha' => now()->toDateString(),
            'estado' => 'anulado',
            'items' => [['idProducto' => $producto->idProducto, 'cantidad' => 4]],
        ]);

        $this->assertSame(10, $producto->fresh()->stock);
        $this->assertSame(2, $vencido->fresh()->cantidad);
        $this->assertSame(3, $proximo->fresh()->cantidad);
        $this->assertSame(5, $sinVencimiento->fresh()->cantidad);
    }

    public function test_venta_rechaza_lotes_vencidos_aunque_el_stock_total_sea_suficiente(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-vencidos@test.com',
            'password' => bcrypt('secret'),
            'rol' => 'administrador',
        ]);
        $categoria = Categoria::create(['nombre' => 'Alimentos']);
        $cliente = Cliente::create(['nombre' => 'Cliente', 'email' => 'cliente-vencidos@test.com']);
        $producto = Producto::create([
            'nombre' => 'Yogur',
            'precio' => 4,
            'stock' => 3,
            'idCategoria' => $categoria->idCategoria,
        ]);
        $loteVencido = $producto->lotes()->create([
            'cantidad' => 3,
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);

        try {
            app(VentaService::class)->crear([
                'idCliente' => $cliente->idCliente,
                'fecha' => now()->toDateString(),
                'items' => [['idProducto' => $producto->idProducto, 'cantidad' => 1]],
            ], $admin->id);
            $this->fail('Una venta no debe consumir inventario vencido.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items', $exception->errors());
        }

        $this->assertSame(3, $producto->fresh()->stock);
        $this->assertSame(3, $loteVencido->fresh()->cantidad);
        $this->assertSame(0, Venta::count());
    }
}
