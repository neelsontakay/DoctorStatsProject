<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Services\AnalysisJobService;

class ReportPolicy
{
    public function __construct(
        private readonly AnalysisJobService $analysisJobService,
    ) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        return $this->analysisJobService->userCanView($user, $report->analysisJob);
    }

    public function update(User $user, Report $report): bool
    {
        return $report->user_id === $user->id
            || ($report->organization !== null && $user->isAdminOf($report->organization));
    }

    public function share(User $user, Report $report): bool
    {
        return $this->view($user, $report);
    }
}
