<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renames the sales tables and foreign keys without recreating them, so
     * existing installations retain every sale, detail, and lot assignment.
     */
    public function up(): void
    {
        if (Schema::hasTable('pedidos') && ! Schema::hasTable('ventas')) {
            Schema::rename('pedidos', 'ventas');
        }

        if (Schema::hasTable('detalle_pedidos') && ! Schema::hasTable('detalle_ventas')) {
            Schema::rename('detalle_pedidos', 'detalle_ventas');
        }

        if (Schema::hasTable('detalle_pedido_lotes') && ! Schema::hasTable('detalle_venta_lotes')) {
            Schema::rename('detalle_pedido_lotes', 'detalle_venta_lotes');
        }

        if (Schema::hasTable('ventas') && Schema::hasColumn('ventas', 'idPedido')) {
            Schema::table('ventas', function (Blueprint $table): void {
                $table->renameColumn('idPedido', 'idVenta');
            });
        }

        if (Schema::hasTable('detalle_ventas') && Schema::hasColumn('detalle_ventas', 'idPedido')) {
            Schema::table('detalle_ventas', function (Blueprint $table): void {
                $table->renameColumn('idPedido', 'idVenta');
            });
        }
    }

    /**
     * Restores the legacy schema if this migration is rolled back.
     */
    public function down(): void
    {
        if (Schema::hasTable('ventas') && Schema::hasColumn('ventas', 'idVenta')) {
            Schema::table('ventas', function (Blueprint $table): void {
                $table->renameColumn('idVenta', 'idPedido');
            });
        }

        if (Schema::hasTable('detalle_ventas') && Schema::hasColumn('detalle_ventas', 'idVenta')) {
            Schema::table('detalle_ventas', function (Blueprint $table): void {
                $table->renameColumn('idVenta', 'idPedido');
            });
        }

        if (Schema::hasTable('detalle_venta_lotes')) {
            Schema::rename('detalle_venta_lotes', 'detalle_pedido_lotes');
        }

        if (Schema::hasTable('detalle_ventas')) {
            Schema::rename('detalle_ventas', 'detalle_pedidos');
        }

        if (Schema::hasTable('ventas')) {
            Schema::rename('ventas', 'pedidos');
        }
    }
};
