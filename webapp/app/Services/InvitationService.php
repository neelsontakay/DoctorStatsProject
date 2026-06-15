<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    private const INVITATION_TTL_DAYS = 7;

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly TransactionalEmailService $transactionalEmailService,
    ) {}

    public function invite(
        Organization $organization,
        User $inviter,
        string $email,
        OrganizationRole $role,
    ): OrganizationInvitation {
        $normalizedEmail = strtolower($email);

        $existingMember = OrganizationMember::query()
            ->where('organization_id', $organization->id)
            ->whereHas('user', fn ($query) => $query->where('email', $normalizedEmail))
            ->where('status', OrganizationMemberStatus::Active)
            ->exists();

        if ($existingMember) {
            throw ValidationException::withMessages([
                'email' => ['This user is already an active member of the organization.'],
            ]);
        }

        $pendingInvitation = OrganizationInvitation::query()
            ->where('organization_id', $organization->id)
            ->where('email', $normalizedEmail)
            ->where('status', InvitationStatus::Pending)
            ->where('expires_at', '>', now())
            ->exists();

        if ($pendingInvitation) {
            throw ValidationException::withMessages([
                'email' => ['A pending invitation already exists for this email address.'],
            ]);
        }

        $invitation = OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'invited_by' => $inviter->id,
            'email' => $normalizedEmail,
            'token' => Str::random(64),
            'role' => $role,
            'expires_at' => now()->addDays(self::INVITATION_TTL_DAYS),
            'status' => InvitationStatus::Pending,
        ]);

        $invitation->load('organization');

        $this->transactionalEmailService->sendMailable(
            $normalizedEmail,
            $normalizedEmail,
            new OrganizationInvitationMail($invitation),
        );

        $this->auditLogService->record(
            'organization.invitation_sent',
            $inviter,
            $organization,
            $invitation,
            ['email' => $normalizedEmail, 'role' => $role->value],
        );

        return $invitation;
    }

    public function revoke(
        Organization $organization,
        OrganizationInvitation $invitation,
        User $actor,
    ): void {
        if ($invitation->organization_id !== $organization->id) {
            abort(404);
        }

        if ($invitation->status !== InvitationStatus::Pending) {
            throw ValidationException::withMessages([
                'invitation' => ['Only pending invitations can be revoked.'],
            ]);
        }

        $invitation->update(['status' => InvitationStatus::Expired]);

        $this->auditLogService->record(
            'organization.invitation_revoked',
            $actor,
            $organization,
            $invitation,
        );
    }

    public function accept(string $token, User $user): OrganizationMember
    {
        return DB::transaction(function () use ($token, $user): OrganizationMember {
            $invitation = OrganizationInvitation::query()
                ->where('token', $token)
                ->firstOrFail();

            if ($invitation->status !== InvitationStatus::Pending) {
                throw ValidationException::withMessages([
                    'token' => ['This invitation is no longer valid.'],
                ]);
            }

            if ($invitation->isExpired()) {
                $invitation->update(['status' => InvitationStatus::Expired]);

                throw ValidationException::withMessages([
                    'token' => ['This invitation has expired.'],
                ]);
            }

            if (strtolower($user->email) !== strtolower($invitation->email)) {
                throw ValidationException::withMessages([
                    'email' => ['This invitation was sent to a different email address.'],
                ]);
            }

            $member = OrganizationMember::query()->updateOrCreate(
                [
                    'organization_id' => $invitation->organization_id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $invitation->role,
                    'status' => OrganizationMemberStatus::Active,
                    'joined_at' => now(),
                ],
            );

            $invitation->update(['status' => InvitationStatus::Accepted]);

            $this->auditLogService->record(
                'organization.invitation_accepted',
                $user,
                $invitation->organization,
                $member,
            );

            return $member->load(['organization', 'user']);
        });
    }
}
