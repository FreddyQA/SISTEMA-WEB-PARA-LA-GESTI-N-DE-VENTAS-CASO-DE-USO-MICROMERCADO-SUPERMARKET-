<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id('idDetalle');

            $table->unsignedBigInteger('idPedido');
            $table->unsignedBigInteger('idProducto');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 8, 2); // precio del producto al momento de la venta
            $table->decimal('subtotal', 8, 2);         // cantidad * precio_unitario

            $table->timestamps();

            // FK pedido: si se borra el pedido, se borran sus líneas
            $table->foreign('idPedido')
                  ->references('idPedido')
                  ->on('pedidos')
                  ->onDelete('cascade');

            // FK producto: no se permite borrar un producto que ya tiene ventas registradas
            $table->foreign('idProducto')
                  ->references('idProducto')
                  ->on('productos')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
