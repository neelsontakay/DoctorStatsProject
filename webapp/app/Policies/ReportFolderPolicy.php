<?php

namespace App\Policies;

use App\Models\ReportFolder;
use App\Models\User;

class ReportFolderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReportFolder $folder): bool
    {
        if ($folder->user_id === $user->id) {
            return true;
        }

        $folder->loadMissing('organization');

        if ($folder->organization !== null && $folder->is_shared) {
            return $user->isMemberOf($folder->organization);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ReportFolder $folder): bool
    {
        return $folder->user_id === $user->id
            || ($folder->organization !== null && $user->isAdminOf($folder->organization));
    }

    public function delete(User $user, ReportFolder $folder): bool
    {
        return $this->update($user, $folder);
    }
}
