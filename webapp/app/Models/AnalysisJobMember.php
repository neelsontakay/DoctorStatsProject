<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisJobMember extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'analysis_job_id',
        'user_id',
    ];

    public function analysisJob(): BelongsTo
    {
        return $this->belongsTo(AnalysisJob::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
