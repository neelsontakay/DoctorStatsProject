<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;

class OrganizationInvitationPolicy
{
    public function create(User $user, Organization $organization): bool
    {
        return $user->isAdminOf($organization);
    }

    public function delete(User $user, OrganizationInvitation $invitation): bool
    {
        return $user->isAdminOf($invitation->organization);
    }
}
