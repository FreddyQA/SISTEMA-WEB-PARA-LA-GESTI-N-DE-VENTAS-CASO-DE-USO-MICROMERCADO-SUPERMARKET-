<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VentaSeeder extends Seeder
{
    public function run(): void
    {
        // Si ya hay ventas demo (re-seed), no duplicar.
        if (Venta::count() > 0) {
            return;
        }

        $clientes  = Cliente::pluck('idCliente', 'email');
        $productos = Producto::pluck('idProducto', 'nombre');
        $dueno     = User::where('email', 'dueno@supermarket.com')->value('id');
        $empleado  = User::where('email', 'empleado@supermarket.com')->value('id');

        $ventasDemo = [
            [
                'cliente' => 'maria.perez@gmail.com', 'usuario' => $dueno, 'estado' => 'completado', 'dias_atras' => 4,
                'detalle' => [
                    ['producto' => 'Mouse Inalámbrico Logitech M185', 'cantidad' => 1, 'precio' => 110.00],
                    ['producto' => 'Cable USB-C 2m Carga Rápida', 'cantidad' => 3, 'precio' => 25.50],
                    ['producto' => 'Bote Galletas Soda 500g', 'cantidad' => 4, 'precio' => 18.50],
                    ['producto' => 'Coca Cola 2L retornable', 'cantidad' => 5, 'precio' => 14.00],
                    ['producto' => 'Chocolate Sublime Clásico 30g', 'cantidad' => 1, 'precio' => 5.50],
                ],
            ],
            [
                'cliente' => 'juan.mamani@yahoo.es', 'usuario' => $dueno, 'estado' => 'completado', 'dias_atras' => 3,
                'detalle' => [
                    ['producto' => 'Cuaderno Universitario 300h. Rivoli', 'cantidad' => 3, 'precio' => 42.00],
                    ['producto' => 'Resma de Papel Bond 80g (500 hojas)', 'cantidad' => 1, 'precio' => 105.00],
                    ['producto' => 'Lápiz de Grafito Faber 2H x12', 'cantidad' => 2, 'precio' => 15.00],
                ],
            ],
            [
                'cliente' => 'ana.condori@hotmail.com', 'usuario' => $dueno, 'estado' => 'completado', 'dias_atras' => 2,
                'detalle' => [
                    ['producto' => 'Martillo Galvanizado 500g Tramontina', 'cantidad' => 2, 'precio' => 52.00],
                    ['producto' => 'Destornillador Phillips STANLEY', 'cantidad' => 5, 'precio' => 21.00],
                    ['producto' => 'Sábanas Queen 2 ½ plazas 100% algodón', 'cantidad' => 2, 'precio' => 185.00],
                ],
            ],
            [
                'cliente' => 'carlos.quispe@outlook.com', 'usuario' => $dueno, 'estado' => 'completado', 'dias_atras' => 1,
                'detalle' => [
                    ['producto' => 'Cloro Clorox 1L', 'cantidad' => 2, 'precio' => 17.50],
                    ['producto' => 'Desinfectante Lysoform 500ml', 'cantidad' => 3, 'precio' => 22.00],
                    ['producto' => 'Jabón líquido para manos 500ml', 'cantidad' => 2, 'precio' => 24.90],
                    ['producto' => 'Escobilla para baño reutilizable', 'cantidad' => 1, 'precio' => 16.50],
                    ['producto' => 'Gel Antibacterial 250ml', 'cantidad' => 1, 'precio' => 22.00],
                ],
            ],
            [
                'cliente' => 'rosa.flores@gmail.com', 'usuario' => $dueno, 'estado' => 'completado', 'dias_atras' => 0,
                'detalle' => [
                    ['producto' => 'Curitas Nexcare pack x50', 'cantidad' => 2, 'precio' => 15.50],
                    ['producto' => 'Alcohol Isopropílico 70% 500ml', 'cantidad' => 3, 'precio' => 19.50],
                    ['producto' => 'Gel Antibacterial 250ml', 'cantidad' => 2, 'precio' => 22.00],
                    ['producto' => 'Shampoo Pantene 400ml Hidratante', 'cantidad' => 1, 'precio' => 48.00],
                    ['producto' => 'Jabón de tocador Nivea pack x3', 'cantidad' => 2, 'precio' => 24.90],
                    ['producto' => 'Termómetro Digital Infrarrojo', 'cantidad' => 1, 'precio' => 85.00],
                ],
            ],
            [
                'cliente' => 'miguel.vargas@empresa.bo', 'usuario' => $empleado, 'estado' => 'anulado', 'dias_atras' => 1,
                'detalle' => [
                    ['producto' => 'Caja de Clavos 2 pulgadas (1kg)', 'cantidad' => 1, 'precio' => 27.00],
                    ['producto' => 'Cinta aisladora 20m x18mm', 'cantidad' => 2, 'precio' => 9.80],
                    ['producto' => 'Pluma Roller BIC Cristal negro x50', 'cantidad' => 3, 'precio' => 38.00],
                ],
            ],
        ];

        foreach ($ventasDemo as $data) {
            DB::transaction(function () use ($data, $clientes, $productos) {
                $total = collect($data['detalle'])->sum(fn ($d) => $d['cantidad'] * $d['precio']);

                $venta = Venta::create([
                    'idCliente' => $clientes[$data['cliente']],
                    'idUsuario' => $data['usuario'],
                    'total'     => $total,
                    'estado'    => $data['estado'],
                    'fecha'     => now()->subDays($data['dias_atras'])->toDateString(),
                ]);

                foreach ($data['detalle'] as $linea) {
                    DetalleVenta::create([
                        'idVenta'        => $venta->idVenta,
                        'idProducto'      => $productos[$linea['producto']],
                        'cantidad'        => $linea['cantidad'],
                        'precio_unitario' => $linea['precio'],
                        'subtotal'        => $linea['cantidad'] * $linea['precio'],
                    ]);
                }
            });
        }
    }
}
