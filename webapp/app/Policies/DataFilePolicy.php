<?php

namespace App\Policies;

use App\Models\DataFile;
use App\Models\User;

class DataFilePolicy
{
    public function view(User $user, DataFile $dataFile): bool
    {
        if ($dataFile->user_id === $user->id) {
            return true;
        }

        return $dataFile->organization_id !== null
            && $user->isMemberOf($dataFile->organization);
    }

    public function delete(User $user, DataFile $dataFile): bool
    {
        return $this->view($user, $dataFile);
    }
}
