<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     password: string,
     *     account_type: string,
     *     organization_name?: string|null,
     * }  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'account_type' => AccountType::from($data['account_type']),
                'status' => UserStatus::Active,
            ]);

            if ($user->account_type === AccountType::Organizational) {
                $organization = Organization::query()->create([
                    'name' => $data['organization_name'],
                    'admin_user_id' => $user->id,
                ]);

                OrganizationMember::query()->create([
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                    'role' => OrganizationRole::Admin,
                    'status' => OrganizationMemberStatus::Active,
                    'joined_at' => now(),
                ]);

                $this->auditLogService->record(
                    'organization.created',
                    $user,
                    $organization,
                    $organization,
                    ['name' => $organization->name],
                );
            }

            $this->auditLogService->record('user.registered', $user, entity: $user);

            event(new Registered($user));

            return $user->fresh(['organizationMemberships.organization']);
        });
    }
}
