<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('pedidos', function (Blueprint $table) {
        $table->id('idPedido');

        $table->unsignedBigInteger('idCliente');
        $table->unsignedBigInteger('idProducto');

        $table->integer('cantidad');
        $table->decimal('total', 8, 2);

        $table->date('fecha');

        $table->timestamps();

        // FK cliente
        $table->foreign('idCliente')
              ->references('idCliente')
              ->on('clientes')
              ->onDelete('cascade');

        // FK producto
        $table->foreign('idProducto')
              ->references('idProducto')
              ->on('productos')
              ->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
