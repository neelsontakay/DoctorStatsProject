<?php

namespace App\Services;

use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Models\AnalysisJob;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $jobsQuery = $this->accessibleJobsQuery($user);

        $jobsByStatus = (clone $jobsQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $recentJobs = (clone $jobsQuery)
            ->with(['organization:id,name'])
            ->latest()
            ->limit(5)
            ->get(['id', 'job_id', 'status', 'objectives', 'organization_id', 'created_at']);

        $subscription = $this->activeSubscriptionFor($user);

        $organizationSummary = null;
        if ($user->account_type->value === 'organizational') {
            $organization = $user->administeredOrganization
                ?? $user->organizationMemberships()
                    ->where('status', OrganizationMemberStatus::Active)
                    ->with('organization')
                    ->first()
                    ?->organization;

            if ($organization instanceof Organization) {
                $organizationSummary = [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'member_count' => $organization->members()
                        ->where('status', OrganizationMemberStatus::Active)
                        ->count(),
                    'shared_analyses_count' => $organization->analysisJobs()->count(),
                ];
            }
        }

        return [
            'jobs_by_status' => $jobsByStatus,
            'total_analyses' => array_sum($jobsByStatus),
            'recent_jobs' => $recentJobs,
            'active_subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan_tier' => $subscription->plan_tier,
                'analyses_used_this_period' => $subscription->analyses_used_this_period,
                'status' => $subscription->status,
            ] : null,
            'organization' => $organizationSummary,
        ];
    }

    private function accessibleJobsQuery(User $user): Builder
    {
        $memberships = $user->organizationMemberships()
            ->where('status', OrganizationMemberStatus::Active)
            ->get();

        return AnalysisJob::query()->where(function (Builder $query) use ($user, $memberships): void {
            $query->where('user_id', $user->id);

            foreach ($memberships as $membership) {
                $query->orWhere(function (Builder $orgQuery) use ($user, $membership): void {
                    $orgQuery->where('organization_id', $membership->organization_id);

                    if ($membership->role === OrganizationRole::Viewer) {
                        $orgQuery->where(function (Builder $viewerQuery) use ($user): void {
                            $viewerQuery
                                ->where('user_id', $user->id)
                                ->orWhereHas(
                                    'jobMembers',
                                    fn (Builder $jobMemberQuery) => $jobMemberQuery->where('user_id', $user->id),
                                );
                        });
                    }
                });
            }
        });
    }

    private function activeSubscriptionFor(User $user): ?Subscription
    {
        if ($user->account_type->value === 'organizational') {
            $organization = $user->administeredOrganization
                ?? $user->organizationMemberships()
                    ->where('status', OrganizationMemberStatus::Active)
                    ->first()
                    ?->organization;

            if ($organization !== null) {
                return $organization->subscriptions()->active()->first();
            }
        }

        return $user->subscriptions()->active()->first();
    }
}
