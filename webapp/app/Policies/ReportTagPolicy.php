<?php

namespace App\Policies;

use App\Models\ReportTag;
use App\Models\User;

class ReportTagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReportTag $tag): bool
    {
        if ($tag->user_id === $user->id) {
            return true;
        }

        $tag->loadMissing('organization');

        if ($tag->organization !== null) {
            return $user->isMemberOf($tag->organization);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ReportTag $tag): bool
    {
        return $tag->user_id === $user->id
            || ($tag->organization !== null && $user->isAdminOf($tag->organization));
    }

    public function delete(User $user, ReportTag $tag): bool
    {
        return $this->update($user, $tag);
    }
}
