<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeVistasModificadasTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginas_modificadas_cargan_para_ambos_roles()
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'a@a.com', 'password' => bcrypt('x'), 'rol' => 'administrador']);
        $invitado = User::create(['name' => 'Invitado', 'email' => 'i@i.com', 'password' => bcrypt('x'), 'rol' => 'invitado']);
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create(['nombre' => 'Producto Test', 'precio' => 10.50, 'stock' => 20, 'idCategoria' => $cat->idCategoria]);
        $cliente = Cliente::create(['nombre' => 'Cliente Test', 'email' => 'cliente@test.com']);

        foreach ([$admin, $invitado] as $user) {
            $this->actingAs($user)->get('/ventas')->assertOk();
            $this->actingAs($user)->get('/ventas/create')->assertOk();
            $this->actingAs($user)->get('/productos')->assertOk();
            $this->actingAs($user)->get("/productos/{$prod->idProducto}/qr")->assertOk();
            $this->actingAs($user)->get("/productos/{$prod->idProducto}/barcode")->assertOk();
        }
    }
}
