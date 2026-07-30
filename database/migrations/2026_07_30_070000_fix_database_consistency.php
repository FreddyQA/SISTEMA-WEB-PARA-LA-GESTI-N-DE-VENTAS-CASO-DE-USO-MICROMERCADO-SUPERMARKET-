<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrige 3 inconsistencias detectadas en la revisión del esquema:
     *
     * 1) productos.idCategoria tenía onDelete('cascade'): borrar una
     *    categoría borraba en cascada (y de forma DEFINITIVA, sin pasar
     *    por el soft delete de productos) todo su inventario. Se cambia
     *    a 'restrict' y se agrega soft delete a categorias, para que una
     *    categoría con productos deba desactivarse, no eliminarse.
     *
     * 2) productos.stock era integer con signo: un bug en el descuento de
     *    stock podía dejarlo en negativo sin que la base de datos lo
     *    impidiera. Se agrega un CHECK a nivel de motor (MySQL 8+).
     *
     * 3) Índices faltantes para las consultas de reportes (fecha de
     *    pedido) y de integridad (email de cliente ya estaba, se agrega
     *    nombre de categoría único para evitar duplicados accidentales).
     */
    public function up(): void
    {
        // 1) categorias: soft delete + FK restrictiva desde productos
        Schema::table('categorias', function (Blueprint $table) {
            $table->softDeletes();
            $table->unique('nombre');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['idCategoria']);
        });
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign('idCategoria')
                  ->references('idCategoria')
                  ->on('categorias')
                  ->onDelete('restrict');
        });

        // 2) stock nunca debe quedar negativo (defensa en profundidad,
        //    además de la validación en la capa de aplicación)
        DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_productos_stock_no_negativo CHECK (stock >= 0)');

        // 3) índice para reportes por fecha
        Schema::table('pedidos', function (Blueprint $table) {
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['fecha']);
        });

        DB::statement('ALTER TABLE productos DROP CONSTRAINT chk_productos_stock_no_negativo');

        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['idCategoria']);
        });
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign('idCategoria')
                  ->references('idCategoria')
                  ->on('categorias')
                  ->onDelete('cascade');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
            $table->dropSoftDeletes();
        });
    }
};
