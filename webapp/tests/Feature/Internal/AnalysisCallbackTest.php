<?php

namespace Tests\Feature\Internal;

use App\Enums\AnalysisJobStatus;
use App\Models\AnalysisJob;
use App\Models\DataFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnalysisCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_service_can_complete_analysis_via_callback(): void
    {
        config(['services.stats_service.token' => 'test-token']);

        Http::fake([
            '*/api/v1/generate-graphs' => Http::response(['graphs' => []], 200),
        ]);

        $user = User::factory()->create();
        $dataFile = DataFile::factory()->create(['user_id' => $user->id]);

        $job = AnalysisJob::query()->create([
            'job_id' => 'DS-2026-CALLBACK1',
            'user_id' => $user->id,
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Analyze treatment outcomes across patient groups. ', 2),
            'status' => AnalysisJobStatus::Processing,
            'access_scope' => 'private',
            'payment_method' => 'pay_per_job',
            'submitted_at' => now(),
        ]);

        $response = $this->postJson('/internal/v1/analysis-callback', [
            'job_id' => $job->job_id,
            'status' => 'completed',
            'progress_percent' => 100,
            'current_step' => 'Completed',
            'data_profile' => [
                'row_count' => 100,
            ],
            'tests' => [
                [
                    'test_name' => 'Independent t-test: age by group',
                    'test_category' => 'hypothesis',
                    'parameters' => ['dependent' => 'age'],
                    'test_statistic' => 2.5,
                    'p_value' => 0.04,
                    'raw_output' => [],
                ],
                [
                    'test_name' => 'Data profile',
                    'test_category' => 'profile',
                    'raw_output' => ['row_count' => 100],
                ],
            ],
        ], [
            'X-Service-Token' => 'test-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'completed');

        $job->refresh();
        $this->assertSame(AnalysisJobStatus::Completed, $job->status);
        $this->assertNotNull($job->completed_at);
        $this->assertDatabaseCount('analysis_results', 2);
    }

    public function test_callback_rejects_invalid_service_token_when_configured(): void
    {
        config(['services.stats_service.token' => 'test-token']);

        $response = $this->postJson('/internal/v1/analysis-callback', [
            'job_id' => 'missing',
            'status' => 'processing',
        ], [
            'X-Service-Token' => 'wrong-token',
        ]);

        $response->assertUnauthorized();
    }
}
