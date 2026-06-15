<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'analysis_job_id',
        'subscription_id',
        'zoho_payment_id',
        'amount',
        'currency',
        'status',
        'payment_type',
        'invoice_number',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'payment_type' => PaymentType::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
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

    public function analysisJob(): BelongsTo
    {
        return $this->belongsTo(AnalysisJob::class);
    }

    public function isSucceeded(): bool
    {
        return $this->status === PaymentStatus::Succeeded;
    }
}
