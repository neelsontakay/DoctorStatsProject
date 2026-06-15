<?php

namespace App\Jobs;

use App\Enums\AnalysisJobStatus;
use App\Models\AnalysisJob;
use App\Services\StatsServiceClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class StartAnalysisJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public AnalysisJob $analysisJob,
    ) {}

    public function handle(StatsServiceClient $statsServiceClient): void
    {
        $this->analysisJob->update([
            'status' => AnalysisJobStatus::Processing,
        ]);

        $analysisId = $statsServiceClient->submitAnalysis($this->analysisJob->fresh());

        if ($analysisId === null) {
            Log::warning('Stats service did not accept the analysis job.', [
                'job_id' => $this->analysisJob->job_id,
            ]);

            throw new RuntimeException(
                "Stats service rejected analysis {$this->analysisJob->job_id}. "
                .'Check that stats-service is running on '
                .config('services.stats_service.url')
                .' and STATS_SERVICE_TOKEN matches on both services.',
            );
        }

        Log::info('Analysis submitted to stats service.', [
            'job_id' => $this->analysisJob->job_id,
            'analysis_id' => $analysisId,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->analysisJob->fresh()?->update([
            'status' => AnalysisJobStatus::Failed,
            'completed_at' => now(),
        ]);

        Log::error('StartAnalysisJob failed permanently.', [
            'job_id' => $this->analysisJob->job_id,
            'message' => $exception?->getMessage(),
        ]);
    }
}
