<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_login_page_shows_branding(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Iniciar Sesión');
        $response->assertSee('Supermarket');
    }
}
