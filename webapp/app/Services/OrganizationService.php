<?php

namespace App\Services;

use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array{name?: string, branding_settings?: array|null}  $data
     */
    public function update(Organization $organization, User $actor, array $data): Organization
    {
        if (isset($data['name'])) {
            $organization->name = $data['name'];
        }

        if (array_key_exists('branding_settings', $data)) {
            $organization->branding_settings = $data['branding_settings'];
        }

        $organization->save();

        $this->auditLogService->record(
            'organization.updated',
            $actor,
            $organization,
            $organization,
            $data,
        );

        return $organization->fresh();
    }

    public function updateMemberRole(
        Organization $organization,
        OrganizationMember $member,
        User $actor,
        OrganizationRole $role,
    ): OrganizationMember {
        if ($member->organization_id !== $organization->id) {
            abort(404);
        }

        if ($member->role === OrganizationRole::Admin && $role !== OrganizationRole::Admin) {
            $this->assertNotLastAdmin($organization, $member);
        }

        $member->update(['role' => $role]);

        if ($role === OrganizationRole::Admin) {
            $organization->update(['admin_user_id' => $member->user_id]);
        }

        $this->auditLogService->record(
            'organization.member_role_updated',
            $actor,
            $organization,
            $member,
            ['role' => $role->value],
        );

        return $member->fresh(['user']);
    }

    public function removeMember(
        Organization $organization,
        OrganizationMember $member,
        User $actor,
    ): void {
        if ($member->organization_id !== $organization->id) {
            abort(404);
        }

        if ($member->role === OrganizationRole::Admin) {
            $this->assertNotLastAdmin($organization, $member);
        }

        $member->update(['status' => OrganizationMemberStatus::Inactive]);

        $this->auditLogService->record(
            'organization.member_removed',
            $actor,
            $organization,
            $member,
        );
    }

    /**
     * @return array{
     *     total_jobs: int,
     *     jobs_by_status: array<string, int>,
     *     jobs_by_member: array<int, array{user_id: int, name: string, count: int}>,
     *     member_count: int,
     *     active_subscription: array<string, mixed>|null,
     * }
     */
    public function analytics(Organization $organization): array
    {
        $jobsByStatus = $organization->analysisJobs()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $jobsByMember = $organization->analysisJobs()
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->get()
            ->map(function ($row) {
                $memberUser = User::query()->find($row->user_id);

                return [
                    'user_id' => $row->user_id,
                    'name' => $memberUser?->name ?? 'Unknown',
                    'count' => (int) $row->count,
                ];
            })
            ->values()
            ->all();

        $subscription = $organization->subscriptions()->active()->first();

        return [
            'total_jobs' => array_sum($jobsByStatus),
            'jobs_by_status' => $jobsByStatus,
            'jobs_by_member' => $jobsByMember,
            'member_count' => $organization->members()
                ->where('status', OrganizationMemberStatus::Active)
                ->count(),
            'active_subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan_tier' => $subscription->plan_tier,
                'analyses_used_this_period' => $subscription->analyses_used_this_period,
                'status' => $subscription->status,
            ] : null,
        ];
    }

    private function assertNotLastAdmin(Organization $organization, OrganizationMember $member): void
    {
        $adminCount = OrganizationMember::query()
            ->where('organization_id', $organization->id)
            ->where('role', OrganizationRole::Admin)
            ->where('status', OrganizationMemberStatus::Active)
            ->count();

        if ($adminCount <= 1 && $member->role === OrganizationRole::Admin) {
            throw ValidationException::withMessages([
                'role' => ['The organization must have at least one administrator.'],
            ]);
        }
    }
}
