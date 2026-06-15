<?php

namespace App\Jobs;

use App\Models\AnalysisJob;
use App\Services\ReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        public AnalysisJob $analysisJob,
    ) {}

    public function handle(ReportService $reportService): void
    {
        set_time_limit((int) config('services.stats_service.timeout', 900));

        try {
            $reportService->generate($this->analysisJob->fresh());
        } catch (Throwable $exception) {
            Log::error('Report generation failed.', [
                'job_id' => $this->analysisJob->job_id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
