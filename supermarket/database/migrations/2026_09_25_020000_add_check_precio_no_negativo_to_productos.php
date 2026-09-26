<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * `productos.stock` ya tiene un CHECK >= 0 a nivel de motor
     * (fix_database_consistency) porque un bug en el descuento de stock
     * podía dejarlo en negativo. `productos.precio` está sujeto a la misma
     * clase de riesgo (un bug futuro en algún cálculo podría insertar un
     * precio negativo) y ya se valida en la capa de aplicación
     * ('precio' => 'min:0' en ProductoController), pero no tenía el mismo
     * refuerzo en la base de datos. Se agrega por el mismo motivo y con
     * la misma limitación ya documentada: MySQL/MariaDB únicamente,
     * porque SQLite no soporta agregar un CHECK a una tabla existente
     * vía ALTER TABLE.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_productos_precio_no_negativo CHECK (precio >= 0)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE productos DROP CONSTRAINT chk_productos_precio_no_negativo');
        }
    }
};
