<?php

namespace App\Models;

use App\Enums\ShareMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportShare extends Model
{
    protected $fillable = [
        'report_id',
        'shared_by',
        'recipient_user_id',
        'organization_id',
        'share_method',
        'token',
        'password_hash',
        'recipient_email',
        'expires_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'share_method' => ShareMethod::class,
            'expires_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function sharer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isAccessible(): bool
    {
        return ! $this->isExpired();
    }
}
