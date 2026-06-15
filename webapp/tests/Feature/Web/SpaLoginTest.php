<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_spa_login_establishes_session_for_protected_api_and_web_routes(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->withHeader('Origin', 'http://127.0.0.1:8080')
            ->get('/sanctum/csrf-cookie')
            ->assertNoContent();

        $this->withHeader('Origin', 'http://127.0.0.1:8080')
            ->withHeader('Referer', 'http://127.0.0.1:8080/login')
            ->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('user.email', 'test@example.com');

        $this->withHeader('Origin', 'http://127.0.0.1:8080')
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'test@example.com');

        $this->get('/dashboard')
            ->assertOk();
    }
}
