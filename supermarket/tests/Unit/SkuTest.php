<?php

namespace Tests\Unit;

use App\Support\Sku;
use PHPUnit\Framework\TestCase;

class SkuTest extends TestCase
{
    public function test_formatea_con_prefijo_y_ceros_a_la_izquierda()
    {
        $this->assertSame('PRD-000001', Sku::format(1));
        $this->assertSame('PRD-000042', Sku::format(42));
    }

    public function test_no_trunca_ids_mas_grandes_que_el_ancho_de_relleno()
    {
        $this->assertSame('PRD-1234567', Sku::format(1234567));
    }
}
