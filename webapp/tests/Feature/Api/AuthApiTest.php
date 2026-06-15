<?php

namespace Tests\Feature\Api;

use App\Enums\AccountType;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_individual_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Dr Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'account_type' => AccountType::Individual->value,
            'accepted_terms' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'jane@example.com')
            ->assertJsonPath('user.account_type', AccountType::Individual->value);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'account_type' => AccountType::Individual->value,
        ]);
    }

    public function test_organizational_registration_creates_organization_and_admin_membership(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Dr Admin',
            'email' => 'admin@hospital.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'account_type' => AccountType::Organizational->value,
            'organization_name' => 'City Hospital',
            'accepted_terms' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.account_type', AccountType::Organizational->value);

        $user = User::query()->where('email', 'admin@hospital.test')->firstOrFail();

        $this->assertDatabaseHas('organizations', [
            'name' => 'City Hospital',
            'admin_user_id' => $user->id,
        ]);

        $organization = Organization::query()->where('name', 'City Hospital')->firstOrFail();

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_verified_user_can_login_and_access_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertOk()
            ->assertJsonPath('user.email', 'login@example.com');

        $this->actingAs($user)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'login@example.com');
    }

    public function test_unverified_user_cannot_access_protected_endpoints(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/me')
            ->assertForbidden();
    }
}
