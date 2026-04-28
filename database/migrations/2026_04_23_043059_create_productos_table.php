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
    Schema::create('productos', function (Blueprint $table) {
        $table->id('idProducto');
        $table->string('nombre', 100);
        $table->decimal('precio', 8, 2);
        $table->integer('stock');
        $table->unsignedBigInteger('idCategoria');
        $table->timestamps();

        // CLAVE FORÁNEA
        $table->foreign('idCategoria')
              ->references('idCategoria')
              ->on('categorias')
              ->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
