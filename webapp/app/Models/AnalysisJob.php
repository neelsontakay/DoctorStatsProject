<?php

namespace App\Models;

use App\Enums\AnalysisAccessScope;
use App\Enums\AnalysisJobStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnalysisJob extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_id',
        'user_id',
        'organization_id',
        'data_file_id',
        'objectives',
        'status',
        'access_scope',
        'payment_method',
        'payment_id',
        'subscription_id',
        'submitted_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AnalysisJobStatus::class,
            'access_scope' => AnalysisAccessScope::class,
            'payment_method' => PaymentMethod::class,
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'job_id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function dataFile(): BelongsTo
    {
        return $this->belongsTo(DataFile::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(AnalysisColumn::class);
    }

    public function jobMembers(): HasMany
    {
        return $this->hasMany(AnalysisJobMember::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(AnalysisResult::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(Report::class);
    }
}
