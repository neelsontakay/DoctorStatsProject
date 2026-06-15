<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'plan_tier',
        'billing_cycle',
        'status',
        'analyses_used_this_period',
        'member_limit',
        'auto_renew',
        'zoho_subscription_id',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'auto_renew' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['active', 'cancelled'])
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }
}
