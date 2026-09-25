<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `productos.nombre` y `categorias.nombre` ya tienen una UNIQUE KEY a
     * nivel de motor como última línea de defensa (ver
     * add_unique_constraint_to_productos_nombre y fix_database_consistency).
     * `proveedores.nombre` seguía la misma regla de negocio en la capa de
     * aplicación (ProveedorController revisa duplicados incluso entre los
     * eliminados con soft delete) pero, por una omisión, nunca recibió el
     * mismo refuerzo en la base de datos. Esta migración cierra esa
     * inconsistencia.
     */
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->unique('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
        });
    }
};
