<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Un pedido pasa a ser una CABECERA: el detalle (qué productos y
     * cuántos) ahora vive en `detalle_pedidos`. También se agrega
     * trazabilidad (qué usuario registró la venta) y estado del pedido.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Ya no aplica: un pedido puede tener varios productos (ver detalle_pedidos)
            $table->dropForeign(['idProducto']);
            $table->dropColumn(['idProducto', 'cantidad']);

            // Ya no queremos que borrar un cliente arrastre su historial de compras
            $table->dropForeign(['idCliente']);
            $table->foreign('idCliente')
                  ->references('idCliente')
                  ->on('clientes')
                  ->onDelete('restrict');

            // Trazabilidad: qué usuario (empleado/dueño) registró la venta
            $table->unsignedBigInteger('idUsuario')->nullable()->after('idCliente');
            $table->foreign('idUsuario')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Estado del pedido
            $table->string('estado', 20)->default('completado')->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['idUsuario']);
            $table->dropColumn(['idUsuario', 'estado']);

            $table->dropForeign(['idCliente']);
            $table->foreign('idCliente')
                  ->references('idCliente')
                  ->on('clientes')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('idProducto')->nullable();
            $table->integer('cantidad')->nullable();
            $table->foreign('idProducto')
                  ->references('idProducto')
                  ->on('productos')
                  ->onDelete('cascade');
        });
    }
};
