<?php

namespace App\Policies;

use App\Models\AnalysisJob;
use App\Models\User;
use App\Services\AnalysisJobService;

class AnalysisJobPolicy
{
    public function __construct(
        private readonly AnalysisJobService $analysisJobService,
    ) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AnalysisJob $analysisJob): bool
    {
        return $this->analysisJobService->userCanView($user, $analysisJob);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function updateAccess(User $user, AnalysisJob $analysisJob): bool
    {
        return $analysisJob->user_id === $user->id
            || ($analysisJob->organization !== null && $user->isAdminOf($analysisJob->organization));
    }
}
