<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\AnalysisAccessScope;
use App\Enums\AnalysisJobStatus;
use App\Enums\ColumnDataType;
use App\Enums\ColumnVariableType;
use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Enums\PaymentMethod;
use App\Jobs\StartAnalysisJob;
use App\Models\AnalysisJob;
use App\Models\DataFile;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AnalysisJobService
{
    public function __construct(
        private readonly PaymentGateService $paymentGateService,
        private readonly SubscriptionService $subscriptionService,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array{
     *     data_file_id: int,
     *     objectives: string,
     *     access_scope: string,
     *     payment_method: string,
     *     payment_id?: int|null,
     *     subscription_id?: int|null,
     *     member_ids?: list<int>,
     *     columns: list<array{
     *         column_name: string,
     *         column_index: int,
     *         data_type: string,
     *         description?: string|null,
     *         unit_of_measurement?: string|null,
     *         variable_type: string,
     *         quality_warnings?: array|null,
     *     }>,
     * }  $data
     */
    public function submit(User $user, array $data): AnalysisJob
    {
        return DB::transaction(function () use ($user, $data): AnalysisJob {
            $dataFile = DataFile::query()->findOrFail($data['data_file_id']);
            $this->assertUserCanUseDataFile($user, $dataFile);

            if (! $dataFile->isReadyForAnalysis()) {
                throw ValidationException::withMessages([
                    'data_file_id' => ['The data file must pass validation before an analysis can be created.'],
                ]);
            }

            $this->assertColumnsAreValid($data['columns']);

            $organization = $this->resolveOrganization($user);
            $accessScope = AnalysisAccessScope::from($data['access_scope']);
            $paymentMethod = PaymentMethod::from($data['payment_method']);

            $this->assertAccessScope($user, $organization, $accessScope, $data['member_ids'] ?? []);

            $paymentResolution = $this->paymentGateService->resolve(
                $user,
                $organization,
                $paymentMethod,
                $data['payment_id'] ?? null,
                $data['subscription_id'] ?? null,
            );

            $job = AnalysisJob::query()->create([
                'job_id' => $this->generateJobId(),
                'user_id' => $user->id,
                'organization_id' => $organization?->id,
                'data_file_id' => $dataFile->id,
                'objectives' => $data['objectives'],
                'status' => AnalysisJobStatus::Pending,
                'access_scope' => $accessScope,
                'payment_method' => $paymentMethod,
                'subscription_id' => $paymentResolution['subscription']?->id,
                'submitted_at' => now(),
            ]);

            if ($paymentResolution['payment'] !== null) {
                $this->paymentGateService->attachPaymentToJob($job, $paymentResolution['payment']);
            }

            if ($paymentResolution['subscription'] !== null) {
                $this->subscriptionService->incrementUsage($paymentResolution['subscription']);
            }

            foreach ($data['columns'] as $column) {
                $job->columns()->create([
                    'column_name' => $column['column_name'],
                    'column_index' => $column['column_index'],
                    'data_type' => ColumnDataType::from($column['data_type']),
                    'description' => $column['description'] ?? null,
                    'unit_of_measurement' => $column['unit_of_measurement'] ?? null,
                    'variable_type' => ColumnVariableType::from($column['variable_type']),
                    'quality_warnings' => $column['quality_warnings'] ?? null,
                ]);
            }

            if ($accessScope === AnalysisAccessScope::SpecificMembers) {
                foreach ($data['member_ids'] as $memberId) {
                    $job->jobMembers()->create(['user_id' => $memberId]);
                }
            }

            $this->auditLogService->record(
                'analysis_job.created',
                $user,
                $organization,
                $job,
                ['job_id' => $job->job_id],
            );

            if (config('queue.default') === 'sync') {
                StartAnalysisJob::dispatchSync($job);
            } else {
                StartAnalysisJob::dispatch($job)->afterCommit();
            }

            return $job->fresh(['columns', 'dataFile', 'organization', 'jobMembers']);
        });
    }

    public function updateAccess(
        AnalysisJob $job,
        User $actor,
        AnalysisAccessScope $accessScope,
        array $memberIds = [],
    ): AnalysisJob {
        if ($job->organization_id === null && $accessScope !== AnalysisAccessScope::Private) {
            throw ValidationException::withMessages([
                'access_scope' => ['Only private access is available for individual analyses.'],
            ]);
        }

        $this->assertAccessScope($actor, $job->organization, $accessScope, $memberIds);

        $job->update(['access_scope' => $accessScope]);
        $job->jobMembers()->delete();

        if ($accessScope === AnalysisAccessScope::SpecificMembers) {
            foreach ($memberIds as $memberId) {
                $job->jobMembers()->create(['user_id' => $memberId]);
            }
        }

        $this->auditLogService->record('analysis_job.access_updated', $actor, $job->organization, $job);

        return $job->fresh(['columns', 'jobMembers']);
    }

    public function accessibleJobsQuery(User $user, ?string $status = null): Builder
    {
        $memberships = $user->organizationMemberships()
            ->where('status', OrganizationMemberStatus::Active)
            ->get();

        $query = AnalysisJob::query()->where(function (Builder $query) use ($user, $memberships): void {
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

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function userCanView(User $user, AnalysisJob $job): bool
    {
        if ($job->user_id === $user->id) {
            return true;
        }

        if ($job->organization_id === null) {
            return false;
        }

        $membership = $user->membershipFor($job->organization);

        if ($membership === null || $membership->status !== OrganizationMemberStatus::Active) {
            return false;
        }

        return match ($job->access_scope) {
            AnalysisAccessScope::AllMembers => true,
            AnalysisAccessScope::Private => $job->user_id === $user->id,
            AnalysisAccessScope::SpecificMembers => $job->jobMembers()->where('user_id', $user->id)->exists(),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $columns
     */
    private function assertColumnsAreValid(array $columns): void
    {
        if ($columns === []) {
            throw ValidationException::withMessages([
                'columns' => ['At least one column mapping is required.'],
            ]);
        }

        $analyzable = collect($columns)->first(
            fn (array $column) => ! in_array(
                $column['variable_type'],
                [ColumnVariableType::Excluded->value, ColumnVariableType::Identifier->value],
                true,
            ),
        );

        if ($analyzable === null) {
            throw ValidationException::withMessages([
                'columns' => ['At least one independent or dependent column is required for analysis.'],
            ]);
        }
    }

    private function assertUserCanUseDataFile(User $user, DataFile $dataFile): void
    {
        if ($dataFile->user_id === $user->id) {
            return;
        }

        if ($dataFile->organization_id !== null && $user->isMemberOf($dataFile->organization)) {
            return;
        }

        abort(403, 'You do not have access to this data file.');
    }

    private function assertAccessScope(
        User $user,
        ?Organization $organization,
        AnalysisAccessScope $accessScope,
        array $memberIds,
    ): void {
        if ($organization === null) {
            if ($accessScope !== AnalysisAccessScope::Private) {
                throw ValidationException::withMessages([
                    'access_scope' => ['Individual accounts can only create private analyses.'],
                ]);
            }

            return;
        }

        if ($accessScope === AnalysisAccessScope::SpecificMembers) {
            if ($memberIds === []) {
                throw ValidationException::withMessages([
                    'member_ids' => ['Select at least one member when using specific member access.'],
                ]);
            }

            $validMemberIds = OrganizationMember::query()
                ->where('organization_id', $organization->id)
                ->where('status', OrganizationMemberStatus::Active)
                ->whereIn('user_id', $memberIds)
                ->pluck('user_id')
                ->all();

            if (count($validMemberIds) !== count(array_unique($memberIds))) {
                throw ValidationException::withMessages([
                    'member_ids' => ['One or more selected members are not active members of the organization.'],
                ]);
            }
        }
    }

    private function resolveOrganization(User $user): ?Organization
    {
        if ($user->account_type !== AccountType::Organizational) {
            return null;
        }

        return $user->administeredOrganization
            ?? $user->organizationMemberships()
                ->where('status', OrganizationMemberStatus::Active)
                ->with('organization')
                ->first()
                ?->organization;
    }

    private function generateJobId(): string
    {
        do {
            $jobId = 'DS-'.now()->format('Y').'-'.strtoupper(Str::random(8));
        } while (AnalysisJob::query()->where('job_id', $jobId)->exists());

        return $jobId;
    }
}
