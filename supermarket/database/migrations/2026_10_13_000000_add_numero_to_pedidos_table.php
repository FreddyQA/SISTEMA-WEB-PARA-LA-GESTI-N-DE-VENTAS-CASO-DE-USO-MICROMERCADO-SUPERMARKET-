<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna 'numero' a la tabla pedidos.
     *
     * Esta columna representa el número de folio visible para el usuario,
     * independiente de la clave primaria autoincremental (idPedido).
     * Cuando se elimina un pedido, los folios se recalculan secuencialmente
     * para que no queden huecos en la numeración (1, 2, 3...).
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->unsignedInteger('numero')->default(0)->after('idPedido');
        });

        // Asigna números secuenciales a los pedidos existentes
        // ordenados por fecha de creación (los más antiguos primero)
        $pedidos = DB::table('pedidos')
            ->whereNull('deleted_at')
            ->orderBy('idPedido')
            ->pluck('idPedido');

        foreach ($pedidos as $index => $id) {
            DB::table('pedidos')
                ->where('idPedido', $id)
                ->update(['numero' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('numero');
        });
    }
};
