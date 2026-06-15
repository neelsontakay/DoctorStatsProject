<?php

namespace Database\Factories;

use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationMember>
 */
class OrganizationMemberFactory extends Factory
{
    protected $model = OrganizationMember::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'role' => OrganizationRole::Analyst,
            'status' => OrganizationMemberStatus::Active,
            'joined_at' => now(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => OrganizationRole::Admin,
        ]);
    }
}
