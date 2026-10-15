<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaginasDeErrorTest extends TestCase
{
    public function test_las_vistas_de_error_personalizadas_renderizan_sin_usuario_autenticado()
    {
        foreach (['404', '419', '429', '500', '503'] as $codigo) {
            $view = view("errors.$codigo");
            $html = $view->render();
            $this->assertStringContainsString('Supermarket', $html);
            $this->assertStringContainsString('Volver al inicio', $html);
        }
    }

    public function test_ruta_inexistente_devuelve_404()
    {
        $this->get('/esta-ruta-no-existe')->assertNotFound();
    }
}
