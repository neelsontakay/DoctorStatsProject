<?php

namespace App\Models;

use App\Enums\DataFileFormat;
use App\Enums\VirusScanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataFile extends Model
{
    /** @use HasFactory<\Database\Factories\DataFileFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'organization_id',
        'original_filename',
        's3_path',
        'format',
        'file_size_bytes',
        'sheet_name',
        'virus_scan_status',
    ];

    protected function casts(): array
    {
        return [
            'format' => DataFileFormat::class,
            'virus_scan_status' => VirusScanStatus::class,
            'file_size_bytes' => 'integer',
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

    public function analysisJobs(): HasMany
    {
        return $this->hasMany(AnalysisJob::class);
    }

    public function isReadyForAnalysis(): bool
    {
        return $this->virus_scan_status === VirusScanStatus::Clean;
    }
}
