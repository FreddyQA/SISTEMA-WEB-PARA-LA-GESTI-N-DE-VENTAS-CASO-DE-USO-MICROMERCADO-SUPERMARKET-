<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = Categoria::pluck('idCategoria', 'nombre');

        $productos = [
            ['Mouse Inalámbrico Logitech M185', 110.00, 45, 'Electrónica'],
            ['Teclado Mecánico Redragon Kumara K552', 320.00, 20, 'Electrónica'],
            ['Cable USB-C 2m Carga Rápida', 25.50, 80, 'Electrónica'],
            ['Auriculares Bluetooth JBL Tune 520BT', 285.00, 30, 'Electrónica'],
            ['Power Bank 20000 mAh Anker', 260.00, 18, 'Electrónica'],
            ['Bote Galletas Soda 500g', 18.50, 120, 'Alimentación'],
            ['Chocolate Sublime Clásico 30g', 5.50, 200, 'Alimentación'],
            ['Coca Cola 2L retornable', 14.00, 90, 'Alimentación'],
            ['Agua Mineral Cielo 1.5L', 7.50, 150, 'Alimentación'],
            ['Yogurt Griego Soprole 170g pack x4', 28.00, 70, 'Alimentación', 28],
            ['Cuaderno Universitario 300h. Rivoli', 42.00, 110, 'Papelería'],
            ['Lápiz de Grafito Faber 2H x12', 15.00, 85, 'Papelería'],
            ['Resma de Papel Bond 80g (500 hojas)', 105.00, 60, 'Papelería'],
            ['Pluma Roller BIC Cristal negro x50', 38.00, 140, 'Papelería'],
            ['Cloro Clorox 1L', 17.50, 88, 'Limpieza'],
            ['Desinfectante Lysoform 500ml', 22.00, 75, 'Limpieza'],
            ['Jabón líquido para manos 500ml', 24.90, 105, 'Limpieza'],
            ['Escobilla para baño reutilizable', 16.50, 60, 'Limpieza'],
            ['Martillo Galvanizado 500g Tramontina', 52.00, 35, 'Ferretería'],
            ['Destornillador Phillips STANLEY', 21.00, 55, 'Ferretería'],
            ['Cinta aisladora 20m x18mm', 9.80, 150, 'Ferretería'],
            ['Caja de Clavos 2 pulgadas (1kg)', 27.00, 90, 'Ferretería'],
            ['Shampoo Pantene 400ml Hidratante', 48.00, 45, 'Belleza'],
            ['Jabón de tocador Nivea pack x3', 24.00, 68, 'Belleza'],
            ['Toalla de Baño Extra Soft Algodón', 78.50, 28, 'Textiles'],
            ['Sábanas Queen 2 ½ plazas 100% algodón', 185.00, 15, 'Textiles'],
            ['Curitas Nexcare pack x50', 15.50, 130, 'Medicina General'],
            ['Alcohol Isopropílico 70% 500ml', 19.50, 70, 'Medicina General'],
            ['Gel Antibacterial 250ml', 22.00, 90, 'Medicina General'],
            ['Termómetro Digital Infrarrojo', 85.00, 12, 'Medicina General'],
        ];

        foreach ($productos as $registro) {
            [$nombre, $precio, $stock, $categoria] = $registro;
            $diasVencimiento = $registro[4] ?? null;
            $producto = Producto::firstOrNew(['nombre' => $nombre]);
            $nuevo = ! $producto->exists;
            $producto->fill([
                'precio' => $precio,
                'idCategoria' => $categorias[$categoria],
            ]);

            if ($nuevo) {
                $producto->stock = $stock;
            }

            $producto->save();

            if ($nuevo) {
                $producto->lotes()->create([
                    'cantidad' => $stock,
                    'fecha_vencimiento' => isset($diasVencimiento) ? now()->addDays($diasVencimiento)->toDateString() : null,
                ]);
            } elseif (! $producto->lotes()->exists() && (int) $producto->stock > 0) {
                $producto->lotes()->create([
                    'cantidad' => $producto->stock,
                    'fecha_vencimiento' => null,
                ]);
            }
        }
    }
}
