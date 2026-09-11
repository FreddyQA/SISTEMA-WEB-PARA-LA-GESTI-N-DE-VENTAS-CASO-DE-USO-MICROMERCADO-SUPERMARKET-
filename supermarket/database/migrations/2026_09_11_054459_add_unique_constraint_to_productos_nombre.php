<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La app ya impedía nombres duplicados de producto (ProductoController
     * revisa incluso los eliminados con soft delete), pero esa regla no
     * estaba reforzada en la base de datos. Esta migración la agrega como
     * última línea de defensa (ej. contra un seeder mal escrito, un import
     * directo por SQL, o una futura ruta de API que no pase por el controlador).
     */
    public function up(): void
    {
        try {
            Schema::table('productos', function (Blueprint $table) {
                $table->unique('nombre');
            });
        } catch (\Throwable $e) {
            // El índice unique ya existe en la base de datos
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
        });
    }
};
