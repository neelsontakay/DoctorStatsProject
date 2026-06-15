<?php

namespace App\Services;

use App\Enums\OrganizationMemberStatus;
use App\Enums\ShareMethod;
use App\Mail\ReportShareMail;
use App\Models\OrganizationMember;
use App\Models\Report;
use App\Models\ReportShare;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportShareService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
        private readonly TransactionalEmailService $transactionalEmailService,
    ) {}

    /**
     * @param  array{
     *     share_method: string,
     *     recipient_email?: string|null,
     *     recipient_user_id?: int|null,
     *     organization_id?: int|null,
     *     password?: string|null,
     *     expires_in_days?: int|null,
     * }  $data
     */
    public function create(Report $report, User $actor, array $data): ReportShare
    {
        $method = ShareMethod::from($data['share_method']);
        $expiresAt = now()->addDays($data['expires_in_days'] ?? config('doctorstats.share_expiry_days'));

        $share = ReportShare::query()->create([
            'report_id' => $report->id,
            'shared_by' => $actor->id,
            'recipient_user_id' => $data['recipient_user_id'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'share_method' => $method,
            'token' => $this->requiresToken($method) ? Str::random(64) : null,
            'password_hash' => isset($data['password']) ? Hash::make($data['password']) : null,
            'recipient_email' => $data['recipient_email'] ?? null,
            'expires_at' => $expiresAt,
        ]);

        if ($method === ShareMethod::Email && $share->recipient_email !== null) {
            $this->transactionalEmailService->sendMailable(
                $share->recipient_email,
                $share->recipient_email,
                new ReportShareMail($share),
            );
        }

        if ($method === ShareMethod::OrgInternal && $share->organization_id !== null) {
            $members = OrganizationMember::query()
                ->where('organization_id', $share->organization_id)
                ->where('status', OrganizationMemberStatus::Active)
                ->with('user')
                ->get();

            foreach ($members as $member) {
                if ($member->user_id === $actor->id) {
                    continue;
                }

                $this->notificationService->reportShared($report, $member->user, $actor);
            }
        }

        if ($share->recipient_user_id !== null) {
            $recipient = User::query()->find($share->recipient_user_id);
            if ($recipient !== null) {
                $this->notificationService->reportShared($report, $recipient, $actor);
            }
        }

        $this->auditLogService->record(
            'report.shared',
            $actor,
            $report->organization,
            $share,
            ['share_method' => $method->value],
        );

        return $share->load(['report', 'sharer']);
    }

    public function revoke(Report $report, ReportShare $share, User $actor): void
    {
        if ($share->report_id !== $report->id) {
            abort(404);
        }

        $share->delete();

        $this->auditLogService->record('report.share_revoked', $actor, $report->organization, $share);
    }

    public function findAccessibleShare(string $token): ReportShare
    {
        $share = ReportShare::query()
            ->where('token', $token)
            ->with('report.analysisJob')
            ->firstOrFail();

        if (! $share->isAccessible()) {
            throw ValidationException::withMessages([
                'token' => ['This share link has expired.'],
            ]);
        }

        return $share;
    }

    public function verifyPassword(ReportShare $share, ?string $password): void
    {
        if ($share->password_hash === null) {
            return;
        }

        if ($password === null || ! Hash::check($password, $share->password_hash)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }
    }

    private function requiresToken(ShareMethod $method): bool
    {
        return in_array($method, [
            ShareMethod::SecureLink,
            ShareMethod::Email,
            ShareMethod::External,
        ], true);
    }
}
