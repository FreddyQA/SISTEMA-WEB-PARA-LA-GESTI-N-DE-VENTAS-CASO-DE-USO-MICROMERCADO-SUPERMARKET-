<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_root_redirects_to_dashboard(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_page_shows_branding(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Supermarket');
        $response->assertSee('Iniciar Sesión');
    }
}
