<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class AccountService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array{name?: string, email?: string}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
            $user->sendEmailVerificationNotification();
        }

        $user->save();

        $this->auditLogService->record('user.profile_updated', $user, entity: $user);

        return $user->fresh();
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $user->forceFill([
            'password' => $newPassword,
        ])->save();

        $this->auditLogService->record('user.password_changed', $user, entity: $user);
    }

    public function deactivate(User $user): void
    {
        $user->forceFill([
            'status' => UserStatus::Deactivated,
        ])->save();

        $this->auditLogService->record('user.deactivated', $user, entity: $user);
    }

    public function markEmailVerified(User $user): User
    {
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
            $this->auditLogService->record('user.email_verified', $user, entity: $user);
        }

        return $user->fresh();
    }
}
