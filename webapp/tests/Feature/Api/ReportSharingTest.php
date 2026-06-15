<?php

namespace Tests\Feature\Api;

use App\Enums\AnalysisJobStatus;
use App\Enums\ReportStatus;
use App\Models\AnalysisJob;
use App\Models\DataFile;
use App\Models\Report;
use App\Models\ReportShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportSharingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['filesystems.default' => 'local']);
        Cache::flush();
    }

    public function test_user_can_create_secure_link_share(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $report = $this->createReportFor($user);

        $response = $this->actingAs($user)->postJson("/api/v1/reports/{$report->id}/shares", [
            'share_method' => 'secure_link',
            'password' => 'secret12',
            'expires_in_days' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonPath('share.share_method', 'secure_link')
            ->assertJsonPath('share.has_password', true);

        $this->assertDatabaseHas('report_shares', [
            'report_id' => $report->id,
            'shared_by' => $user->id,
            'share_method' => 'secure_link',
        ]);
    }

    public function test_public_shared_report_requires_password_unlock(): void
    {
        $user = User::factory()->create();
        $report = $this->createReportFor($user);

        $share = ReportShare::query()->create([
            'report_id' => $report->id,
            'shared_by' => $user->id,
            'share_method' => 'secure_link',
            'token' => 'public-share-token',
            'password_hash' => bcrypt('secret12'),
            'expires_at' => now()->addDay(),
        ]);

        $this->getJson('/api/v1/shared/public-share-token')
            ->assertUnauthorized()
            ->assertJsonPath('password_required', true);

        $this->postJson('/api/v1/shared/public-share-token/unlock', [
            'password' => 'secret12',
        ])->assertOk();

        $this->getJson('/api/v1/shared/public-share-token')
            ->assertOk()
            ->assertJsonPath('data.report.id', $report->id);
    }

    private function createReportFor(User $user): Report
    {
        $dataFile = DataFile::factory()->create(['user_id' => $user->id]);
        $job = AnalysisJob::query()->create([
            'job_id' => 'DS-2026-SHARE01',
            'user_id' => $user->id,
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Analyze treatment outcomes across patient groups. ', 2),
            'status' => AnalysisJobStatus::Completed,
            'access_scope' => 'private',
            'payment_method' => 'pay_per_job',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);

        $htmlPath = 'reports/DS-2026-SHARE01/report.html';
        Storage::disk('local')->put($htmlPath, '<html><body>Report</body></html>');

        return Report::query()->create([
            'analysis_job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Shared report',
            'web_html_path' => $htmlPath,
            'status' => ReportStatus::Published,
        ]);
    }
}
