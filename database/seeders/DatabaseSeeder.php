<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // DUEÑO (Administrador)
        User::create([
            'name'     => 'Freddie Ascarraga',
            'email'    => 'dueno@supermarket.com',
            'password' => Hash::make('password123'),
            'rol'      => 'administrador',
        ]);

        // EMPLEADO (Invitado)
        User::create([
            'name'     => 'Empleado Demo',
            'email'    => 'empleado@supermarket.com',
            'password' => Hash::make('password123'),
            'rol'      => 'invitado',
        ]);
    }
}