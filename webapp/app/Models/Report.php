<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'analysis_job_id',
        'user_id',
        'organization_id',
        'report_folder_id',
        'title',
        'executive_summary',
        'ai_interpretation',
        'web_html_path',
        'pdf_path',
        'excel_path',
        'is_favourite',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'is_favourite' => 'boolean',
        ];
    }

    public function analysisJob(): BelongsTo
    {
        return $this->belongsTo(AnalysisJob::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ReportFolder::class, 'report_folder_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ReportTag::class, 'report_report_tag');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ReportShare::class);
    }
}
