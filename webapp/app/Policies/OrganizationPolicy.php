<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $user->isMemberOf($organization);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    public function manageMembers(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    public function viewAnalytics(User $user, Organization $organization): bool
    {
        return $user->isMemberOf($organization);
    }
}
