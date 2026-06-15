<?php

namespace App\Models;

use App\Enums\ColumnDataType;
use App\Enums\ColumnVariableType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisColumn extends Model
{
    protected $fillable = [
        'analysis_job_id',
        'column_name',
        'column_index',
        'data_type',
        'description',
        'unit_of_measurement',
        'variable_type',
        'quality_warnings',
    ];

    protected function casts(): array
    {
        return [
            'data_type' => ColumnDataType::class,
            'variable_type' => ColumnVariableType::class,
            'quality_warnings' => 'array',
            'column_index' => 'integer',
        ];
    }

    public function analysisJob(): BelongsTo
    {
        return $this->belongsTo(AnalysisJob::class);
    }
}
