<?php

namespace App\Support;

/**
 * Formato único de SKU para productos. Antes de esta clase, el mismo
 * formato ("PRD-" + id con ceros a la izquierda) estaba repetido en
 * 5 lugares distintos del código, y dos de ellos usaban un ancho de
 * relleno diferente (5 dígitos vs 6), generando SKUs distintos para
 * el mismo producto según dónde se mirara.
 */
class Sku
{
    private const PREFIJO = 'PRD-';
    private const ANCHO = 6;

    public static function format(int $idProducto): string
    {
        return self::PREFIJO . str_pad((string) $idProducto, self::ANCHO, '0', STR_PAD_LEFT);
    }
}
