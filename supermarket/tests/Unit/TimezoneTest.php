<?php

namespace Tests\Unit;

use Tests\TestCase;

class TimezoneTest extends TestCase
{
    public function test_la_aplicacion_usa_el_horario_de_bolivia()
    {
        $this->assertSame('America/La_Paz', config('app.timezone'));
        $this->assertSame('America/La_Paz', now()->timezoneName);
    }
}
