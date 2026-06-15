<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationInvitation extends Model
{
    protected $fillable = [
        'organization_id',
        'invited_by',
        'email',
        'token',
        'role',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'role' => OrganizationRole::class,
            'status' => InvitationStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === InvitationStatus::Pending && ! $this->isExpired();
    }
}
