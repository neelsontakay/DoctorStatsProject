<?php

namespace App\Services;

use App\Enums\AnalysisJobStatus;
use App\Jobs\GenerateReportJob;
use App\Models\AnalysisJob;
use App\Models\AnalysisResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnalysisCallbackService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array{
     *     job_id: string,
     *     status: string,
     *     progress_percent?: int,
     *     current_step?: string|null,
     *     error?: string|null,
     *     data_profile?: array|null,
     *     tests?: list<array<string, mixed>>|null,
     * }  $payload
     */
    public function handle(array $payload): AnalysisJob
    {
        $job = AnalysisJob::query()->where('job_id', $payload['job_id'])->firstOrFail();

        return match ($payload['status']) {
            'processing' => $this->markProcessing($job, $payload),
            'completed' => $this->markCompleted($job, $payload),
            'failed' => $this->markFailed($job, $payload),
            default => throw ValidationException::withMessages([
                'status' => ['Unsupported analysis callback status.'],
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function markProcessing(AnalysisJob $job, array $payload): AnalysisJob
    {
        if ($job->status === AnalysisJobStatus::Completed) {
            return $job;
        }

        $job->update([
            'status' => AnalysisJobStatus::Processing,
        ]);

        return $job->fresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function markCompleted(AnalysisJob $job, array $payload): AnalysisJob
    {
        if ($job->status === AnalysisJobStatus::Completed) {
            return $job;
        }

        return DB::transaction(function () use ($job, $payload): AnalysisJob {
            $job->results()->delete();

            $tests = $payload['tests'] ?? [];
            $hasProfileResult = collect($tests)->contains(
                fn (array $test): bool => ($test['test_category'] ?? '') === 'profile',
            );

            foreach ($tests as $test) {
                AnalysisResult::query()->create([
                    'analysis_job_id' => $job->id,
                    'test_name' => $test['test_name'],
                    'test_category' => $test['test_category'],
                    'parameters' => $test['parameters'] ?? null,
                    'test_statistic' => $test['test_statistic'] ?? null,
                    'p_value' => $test['p_value'] ?? null,
                    'confidence_intervals' => $test['confidence_intervals'] ?? null,
                    'effect_sizes' => $test['effect_sizes'] ?? null,
                    'assumptions_validation' => $test['assumptions_validation'] ?? null,
                    'raw_output' => $test['raw_output'] ?? null,
                ]);
            }

            if (! $hasProfileResult && ! empty($payload['data_profile'])) {
                AnalysisResult::query()->create([
                    'analysis_job_id' => $job->id,
                    'test_name' => 'Data profile',
                    'test_category' => 'profile',
                    'raw_output' => $payload['data_profile'],
                ]);
            }

            $job->update([
                'status' => AnalysisJobStatus::Completed,
                'completed_at' => now(),
            ]);

            $this->auditLogService->record(
                'analysis_job.completed',
                $job->user,
                $job->organization,
                $job,
                ['job_id' => $job->job_id],
            );

            // Reports can take minutes (graphs + AI). Queue on database so the stats callback returns quickly.
            GenerateReportJob::dispatch($job)->onConnection('database');

            return $job->fresh(['results']);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function markFailed(AnalysisJob $job, array $payload): AnalysisJob
    {
        if ($job->status === AnalysisJobStatus::Completed) {
            return $job;
        }

        $job->update([
            'status' => AnalysisJobStatus::Failed,
            'completed_at' => now(),
        ]);

        $this->auditLogService->record(
            'analysis_job.failed',
            $job->user,
            $job->organization,
            $job,
            [
                'job_id' => $job->job_id,
                'error' => $payload['error'] ?? null,
            ],
        );

        return $job->fresh();
    }
}
