<?php

namespace Tests\Feature\Api;

use App\Enums\AnalysisJobStatus;
use App\Enums\VirusScanStatus;
use App\Jobs\GenerateReportJob;
use App\Models\AnalysisJob;
use App\Models\AnalysisResult;
use App\Models\DataFile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('filesystems.default', 'local');
        Config::set('doctorstats.ai_stub_enabled', true);
        Storage::fake('local');
    }

    public function test_generate_report_job_creates_published_report_with_html(): void
    {
        Http::fake([
            '*/api/v1/generate-graphs' => Http::response([
                'analysis_id' => 'DS-2026-REPORT1',
                'graphs' => [
                    [
                        'graph_type' => 'histogram',
                        'title' => 'Age Distribution',
                        'caption' => 'Distribution of age',
                        'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
                        'mime_type' => 'image/png',
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create();
        $dataFile = DataFile::factory()->create([
            'user_id' => $user->id,
            'virus_scan_status' => VirusScanStatus::Clean,
        ]);

        Storage::disk('local')->put(
            $dataFile->s3_path,
            "patient_id,age,group\n1,45,A\n2,52,B\n",
        );

        $job = AnalysisJob::query()->create([
            'job_id' => 'DS-2026-REPORT1',
            'user_id' => $user->id,
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Analyze treatment outcomes across patient groups. ', 2),
            'status' => AnalysisJobStatus::Completed,
            'access_scope' => 'private',
            'payment_method' => 'pay_per_job',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);

        AnalysisResult::query()->create([
            'analysis_job_id' => $job->id,
            'test_name' => 'Independent t-test: age by group',
            'test_category' => 'hypothesis',
            'test_statistic' => 1.23,
            'p_value' => 0.04,
        ]);

        AnalysisResult::query()->create([
            'analysis_job_id' => $job->id,
            'test_name' => 'Data profile',
            'test_category' => 'profile',
            'raw_output' => ['row_count' => 2],
        ]);

        GenerateReportJob::dispatchSync($job);

        $report = Report::query()->where('analysis_job_id', $job->id)->first();
        $this->assertNotNull($report);
        $this->assertSame('published', $report->status->value);
        $this->assertNotNull($report->executive_summary);
        $this->assertNotNull($report->web_html_path);
        Storage::disk('local')->assertExists($report->web_html_path);

        $this->actingAs($user)
            ->getJson("/api/v1/reports/{$report->id}")
            ->assertOk()
            ->assertJsonPath('data.job_id', 'DS-2026-REPORT1');

        $this->actingAs($user)
            ->get("/api/v1/reports/{$report->id}/view")
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'analysis_complete',
        ]);
    }
}
