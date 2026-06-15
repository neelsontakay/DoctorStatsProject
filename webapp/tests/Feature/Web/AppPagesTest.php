<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_landing_and_auth_pages(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_authenticated_user_can_view_app_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();

        $this->actingAs($user)
            ->get('/analyses')
            ->assertOk();

        $this->actingAs($user)
            ->get('/analyses/create')
            ->assertOk();

        $this->actingAs($user)
            ->get('/reports')
            ->assertOk();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk();

        $this->actingAs($user)
            ->get('/organization')
            ->assertOk();

        $this->actingAs($user)
            ->get('/analysis/demo')
            ->assertOk()
            ->assertSee('Demo clinical analysis', false);
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_landing_page_shows_prototype_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Statistical analysis for clinical research', false)
            ->assertSee('Key Features', false)
            ->assertSee('How It Works', false);
    }
}
