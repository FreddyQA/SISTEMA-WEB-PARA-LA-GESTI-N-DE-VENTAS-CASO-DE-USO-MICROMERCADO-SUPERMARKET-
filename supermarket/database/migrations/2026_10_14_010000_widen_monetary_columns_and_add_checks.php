<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 1) Ampliación monetaria: de decimal(8,2) [máx 999,999.99] a decimal(12,2)
     *    [hasta 9,999,999,999.99] para admitir ventas mayoristas y acumulados
     *    sin desbordar las columnas de total, subtotal y precio.
     *
     * 2) Restricciones de integridad a nivel de base de datos (CHECKs):
     *    - pedidos.estado debe ser ('completado', 'pendiente', 'anulado').
     *    - users.rol debe ser ('administrador', 'invitado').
     *    - detalle_pedidos.subtotal y precio_unitario no pueden ser negativos.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1) Ampliar precisión decimal en tablas monetarias
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE pedidos MODIFY total DECIMAL(12, 2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE detalle_pedidos MODIFY precio_unitario DECIMAL(12, 2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE detalle_pedidos MODIFY subtotal DECIMAL(12, 2) NOT NULL DEFAULT 0.00');
            DB::statement('ALTER TABLE productos MODIFY precio DECIMAL(12, 2) NOT NULL DEFAULT 0.00');

            // 2) Restricciones CHECK a nivel de motor relacional
            DB::statement("ALTER TABLE pedidos ADD CONSTRAINT chk_pedidos_estado CHECK (estado IN ('completado', 'pendiente', 'anulado'))");
            DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_rol CHECK (rol IN ('administrador', 'invitado'))");
            DB::statement("ALTER TABLE detalle_pedidos ADD CONSTRAINT chk_detalle_pedidos_subtotal CHECK (subtotal >= 0)");
            DB::statement("ALTER TABLE detalle_pedidos ADD CONSTRAINT chk_detalle_pedidos_precio CHECK (precio_unitario >= 0)");
        } else {
            // SQLite o soporte genérico
            Schema::table('pedidos', function (Blueprint $table) {
                $table->decimal('total', 12, 2)->default(0)->change();
            });
            Schema::table('detalle_pedidos', function (Blueprint $table) {
                $table->decimal('precio_unitario', 12, 2)->default(0)->change();
                $table->decimal('subtotal', 12, 2)->default(0)->change();
            });
            Schema::table('productos', function (Blueprint $table) {
                $table->decimal('precio', 12, 2)->default(0)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            try { DB::statement('ALTER TABLE pedidos DROP CONSTRAINT chk_pedidos_estado'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE users DROP CONSTRAINT chk_users_rol'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE detalle_pedidos DROP CONSTRAINT chk_detalle_pedidos_subtotal'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE detalle_pedidos DROP CONSTRAINT chk_detalle_pedidos_precio'); } catch (\Throwable $e) {}

            DB::statement('ALTER TABLE pedidos MODIFY total DECIMAL(8, 2) NOT NULL');
            DB::statement('ALTER TABLE detalle_pedidos MODIFY precio_unitario DECIMAL(8, 2) NOT NULL');
            DB::statement('ALTER TABLE detalle_pedidos MODIFY subtotal DECIMAL(8, 2) NOT NULL');
            DB::statement('ALTER TABLE productos MODIFY precio DECIMAL(8, 2) NOT NULL');
        } else {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->decimal('total', 8, 2)->change();
            });
            Schema::table('detalle_pedidos', function (Blueprint $table) {
                $table->decimal('precio_unitario', 8, 2)->change();
                $table->decimal('subtotal', 8, 2)->change();
            });
            Schema::table('productos', function (Blueprint $table) {
                $table->decimal('precio', 8, 2)->change();
            });
        }
    }
};
