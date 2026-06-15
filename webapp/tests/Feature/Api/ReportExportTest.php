<?php

namespace Tests\Feature\Api;

use App\Enums\AnalysisJobStatus;
use App\Enums\ReportStatus;
use App\Models\AnalysisJob;
use App\Models\AnalysisResult;
use App\Models\DataFile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['filesystems.default' => 'local']);
    }

    public function test_user_can_download_excel_export(): void
    {
        $user = User::factory()->create();
        $report = $this->createReportFor($user);

        AnalysisResult::query()->create([
            'analysis_job_id' => $report->analysis_job_id,
            'test_name' => 'Independent t-test',
            'test_category' => 'hypothesis',
            'test_statistic' => 2.1,
            'p_value' => 0.03,
        ]);

        $response = $this->actingAs($user)
            ->get("/api/v1/reports/{$report->id}/download/excel");

        $response->assertOk();
        $report->refresh();
        $this->assertNotNull($report->excel_path);
        Storage::disk('local')->assertExists($report->excel_path);
    }

    private function createReportFor(User $user): Report
    {
        $dataFile = DataFile::factory()->create(['user_id' => $user->id]);
        $job = AnalysisJob::query()->create([
            'job_id' => 'DS-2026-EXPORT1',
            'user_id' => $user->id,
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Analyze treatment outcomes across patient groups. ', 2),
            'status' => AnalysisJobStatus::Completed,
            'access_scope' => 'private',
            'payment_method' => 'pay_per_job',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);

        $htmlPath = 'reports/DS-2026-EXPORT1/report.html';
        Storage::disk('local')->put($htmlPath, '<html><body><h1>Report</h1></body></html>');

        return Report::query()->create([
            'analysis_job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Export report',
            'executive_summary' => 'Summary text',
            'web_html_path' => $htmlPath,
            'status' => ReportStatus::Published,
        ]);
    }
}
