<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes_inventario', function (Blueprint $table) {
            $table->id('idLote');
            $table->unsignedBigInteger('idProducto');
            $table->unsignedInteger('cantidad')->default(0);
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
            $table->foreign('idProducto')
                ->references('idProducto')
                ->on('productos')
                ->cascadeOnDelete();
            $table->index(['idProducto', 'fecha_vencimiento', 'cantidad']);
        });

        DB::table('productos')
            ->where('stock', '>', 0)
            ->orderBy('idProducto')
            ->chunkById(500, function ($productos): void {
                foreach ($productos as $producto) {
                    DB::table('lotes_inventario')->insert([
                        'idProducto' => $producto->idProducto,
                        'cantidad' => $producto->stock,
                        'fecha_vencimiento' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }, 'idProducto');

        Schema::create('detalle_pedido_lotes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idDetalle');
            $table->unsignedBigInteger('idLote');
            $table->unsignedInteger('cantidad');
            $table->foreign('idDetalle')
                ->references('idDetalle')
                ->on('detalle_pedidos')
                ->cascadeOnDelete();
            $table->foreign('idLote')
                ->references('idLote')
                ->on('lotes_inventario')
                ->restrictOnDelete();
            $table->unique(['idDetalle', 'idLote']);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['idProveedor']);
            $table->dropColumn('idProveedor');
        });
        Schema::dropIfExists('proveedores');
    }

    public function down(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('idProveedor');
            $table->string('nombre', 150);
            $table->string('nit', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedBigInteger('idProveedor')->nullable();
            $table->foreign('idProveedor')
                ->references('idProveedor')
                ->on('proveedores')
                ->nullOnDelete();
        });

        Schema::dropIfExists('detalle_pedido_lotes');
        Schema::dropIfExists('lotes_inventario');
    }
};
