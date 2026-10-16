<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Electrónica',      'descripcion' => 'Dispositivos electrónicos, cables, periféricos y accesorios.'],
            ['nombre' => 'Alimentación',     'descripcion' => 'Snacks, bebidas, alimentos empaquetados y de consumo diario.'],
            ['nombre' => 'Papelería',        'descripcion' => 'Útiles de oficina, cuadernos, lápices, resmas y artículos escolares.'],
            ['nombre' => 'Limpieza',         'descripcion' => 'Productos de limpieza doméstica e institucional, desinfectantes.'],
            ['nombre' => 'Ferretería',       'descripcion' => 'Herramientas manuales y eléctricas, clavos, tornillos, adhesivos.'],
            ['nombre' => 'Belleza',          'descripcion' => 'Artículos de cuidado personal, perfumería, cosméticos.'],
            ['nombre' => 'Textiles',         'descripcion' => 'Uniformes, ropa de cama, toallas y accesorios textiles.'],
            ['nombre' => 'Medicina General', 'descripcion' => 'Artículos de botiquín, curitas, alcohol, gel antibacterial.'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(['nombre' => $categoria['nombre']], $categoria);
        }
    }
}
