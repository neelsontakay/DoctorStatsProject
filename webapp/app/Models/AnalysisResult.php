<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisResult extends Model
{
    protected $fillable = [
        'analysis_job_id',
        'test_name',
        'test_category',
        'parameters',
        'test_statistic',
        'p_value',
        'confidence_intervals',
        'effect_sizes',
        'assumptions_validation',
        'raw_output',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'test_statistic' => 'decimal:6',
            'p_value' => 'decimal:10',
            'confidence_intervals' => 'array',
            'effect_sizes' => 'array',
            'assumptions_validation' => 'array',
            'raw_output' => 'array',
        ];
    }

    public function analysisJob(): BelongsTo
    {
        return $this->belongsTo(AnalysisJob::class);
    }
}
