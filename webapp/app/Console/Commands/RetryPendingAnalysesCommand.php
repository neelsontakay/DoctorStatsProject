<?php

namespace App\Console\Commands;

use App\Enums\AnalysisJobStatus;
use App\Jobs\StartAnalysisJob;
use App\Models\AnalysisJob;
use Illuminate\Console\Command;

class RetryPendingAnalysesCommand extends Command
{
    protected $signature = 'analysis:retry-pending {--job= : Retry a specific job_id only}';

    protected $description = 'Re-queue StartAnalysisJob for analyses stuck in pending status';

    public function handle(): int
    {
        $query = AnalysisJob::query()->where('status', AnalysisJobStatus::Pending);

        if ($jobId = $this->option('job')) {
            $query->where('job_id', $jobId);
        }

        $jobs = $query->orderBy('id')->get();

        if ($jobs->isEmpty()) {
            $this->info('No pending analyses to retry.');

            return self::SUCCESS;
        }

        foreach ($jobs as $job) {
            StartAnalysisJob::dispatch($job);
            $this->line("Queued retry for {$job->job_id}");
        }

        $this->info("Dispatched {$jobs->count()} analysis job(s). Ensure queue:work and stats-service are running.");

        return self::SUCCESS;
    }
}
