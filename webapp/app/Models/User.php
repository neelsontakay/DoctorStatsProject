<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\OrganizationMemberStatus;
use App\Enums\OrganizationRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'account_type',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'account_type' => AccountType::class,
            'status' => UserStatus::class,
        ];
    }

    public function organizationMemberships(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function administeredOrganization(): HasOne
    {
        return $this->hasOne(Organization::class, 'admin_user_id');
    }

    public function analysisJobs(): HasMany
    {
        return $this->hasMany(AnalysisJob::class);
    }

    public function analysisJobMemberships(): HasMany
    {
        return $this->hasMany(AnalysisJobMember::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function membershipFor(Organization $organization): ?OrganizationMember
    {
        return $this->organizationMemberships()
            ->where('organization_id', $organization->id)
            ->first();
    }

    public function isMemberOf(Organization $organization): bool
    {
        $membership = $this->membershipFor($organization);

        return $membership !== null
            && $membership->status === OrganizationMemberStatus::Active;
    }

    public function isAdminOf(Organization $organization): bool
    {
        $membership = $this->membershipFor($organization);

        return $membership !== null
            && $membership->isActive()
            && $membership->role === OrganizationRole::Admin;
    }
}
