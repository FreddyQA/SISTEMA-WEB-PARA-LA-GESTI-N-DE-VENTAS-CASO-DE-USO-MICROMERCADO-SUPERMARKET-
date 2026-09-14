<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índice compuesto (estado, fecha): las consultas de Reportes y del
     * Dashboard siempre combinan "estado != anulado" con un filtro/orden
     * por fecha. `fecha` ya tenía su propio índice; este compuesto ayuda
     * a que el motor resuelva ambas condiciones con un solo recorrido de
     * índice en vez de filtrar "estado" fila por fila después de usar
     * el índice de fecha.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->index(['estado', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['estado', 'fecha']);
        });
    }
};
