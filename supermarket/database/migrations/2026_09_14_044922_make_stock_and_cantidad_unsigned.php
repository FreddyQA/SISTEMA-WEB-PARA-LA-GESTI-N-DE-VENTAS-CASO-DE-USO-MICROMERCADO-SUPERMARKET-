<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * `productos.stock` y `detalle_pedidos.cantidad` nunca deben ser
     * negativos por definición de negocio. Ya existía un CHECK a nivel
     * de motor para stock (ver fix_database_consistency), pero el tipo
     * de columna seguía siendo un entero CON signo en ambas tablas.
     * UNSIGNED es una segunda capa de defensa que además documenta la
     * intención de la columna a cualquiera que lea el esquema directamente
     * (ej. abriendo la base en MySQL-Front), sin depender de leer el CHECK.
     *
     * No se usa Schema::table()->change() porque este proyecto no tiene
     * instalado doctrine/dbal (requerido por ese método); se usa SQL
     * crudo, igual que ya se hace para el CHECK de stock en la migración
     * fix_database_consistency. Por el mismo motivo que esa migración,
     * esto solo aplica en MySQL/MariaDB: SQLite no tiene un tipo INT
     * UNSIGNED real (lo trata como un alias de INTEGER con signo), así
     * que ahí la regla sigue a cargo únicamente de la capa de aplicación.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE productos MODIFY stock INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE detalle_pedidos MODIFY cantidad INT UNSIGNED NOT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE productos MODIFY stock INT NOT NULL');
            DB::statement('ALTER TABLE detalle_pedidos MODIFY cantidad INT NOT NULL');
        }
    }
};
