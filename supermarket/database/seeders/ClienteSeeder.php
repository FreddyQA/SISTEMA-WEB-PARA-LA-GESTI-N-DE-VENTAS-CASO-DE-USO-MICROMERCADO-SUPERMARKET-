<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            ['nombre' => 'María Pérez Quispe',       'email' => 'maria.perez@gmail.com',     'telefono' => '+591 70001111', 'direccion' => 'Zona Sur #234, La Paz'],
            ['nombre' => 'Juan Carlos Mamani',        'email' => 'juan.mamani@yahoo.es',      'telefono' => '+591 71112222', 'direccion' => 'Calle Junín #567, Cochabamba'],
            ['nombre' => 'Ana Patricia Condori',      'email' => 'ana.condori@hotmail.com',   'telefono' => '+591 72223333', 'direccion' => 'Av. Beni #432, Santa Cruz'],
            ['nombre' => 'Carlos Saúl Quispe',        'email' => 'carlos.quispe@outlook.com', 'telefono' => '+591 73334444', 'direccion' => 'Av. Pando #123, El Alto'],
            ['nombre' => 'Rosa Elena Flores',         'email' => 'rosa.flores@gmail.com',     'telefono' => '+591 74445555', 'direccion' => 'Zona Miraflores, Oruro'],
            ['nombre' => 'Miguel Ángel Vargas',       'email' => 'miguel.vargas@empresa.bo',  'telefono' => '+591 75556666', 'direccion' => 'Plaza Principal, Potosí'],
            ['nombre' => 'Empresa Constructora A+',   'email' => 'ventas@a-constructora.com', 'telefono' => '+591 2 2881111', 'direccion' => 'Zona Industrial #5, Santa Cruz'],
        ];

        foreach ($clientes as $cliente) {
            Cliente::updateOrCreate(['email' => $cliente['email']], $cliente);
        }
    }
}
