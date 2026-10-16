<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Orquesta el seed completo de datos de demostración.
     *
     * Usa los mismos nombres/emails/contraseñas que
     * database/schema_supermarket_completo.sql, para que no importe qué
     * camino se use para levantar la base de datos (migrate --seed o
     * importar el .sql): el resultado es el mismo.
     *
     * ADVERTENCIA DE SEGURIDAD: las contraseñas por defecto ('password123')
     * son solo para demostración/desarrollo local. Antes de poner el
     * sistema a operar con datos reales del negocio, cambie la contraseña
     * de 'dueno@supermarket.com' desde el módulo de Usuarios (o defina
     * SEED_ADMIN_PASSWORD / SEED_EMPLEADO_PASSWORD en el .env antes de
     * correr el seeder por primera vez). Dejar la contraseña de fábrica en
     * un sistema que ya está en producción es una falla de seguridad, no
     * un detalle menor: el código fuente (con este mismo valor) es
     * público en el repositorio del proyecto.
     */
    public function run(): void
    {
        // DUEÑO (Administrador)
        User::updateOrCreate(
            ['email' => 'dueno@supermarket.com'],
            [
                'name'     => 'Freddie Ascarraga',
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'password123')),
                'rol'      => 'administrador',
            ]
        );

        // EMPLEADO (Invitado)
        User::updateOrCreate(
            ['email' => 'empleado@supermarket.com'],
            [
                'name'     => 'Empleado Demo',
                'password' => Hash::make(env('SEED_EMPLEADO_PASSWORD', 'password123')),
                'rol'      => 'invitado',
            ]
        );

        $this->call([
            CategoriaSeeder::class,
            ProductoSeeder::class,
            ClienteSeeder::class,
            VentaSeeder::class,
        ]);
    }
}
