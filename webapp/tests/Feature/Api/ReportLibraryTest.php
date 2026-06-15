<?php

namespace Tests\Feature\Api;

use App\Enums\AnalysisJobStatus;
use App\Enums\ReportStatus;
use App\Models\AnalysisJob;
use App\Models\DataFile;
use App\Models\Report;
use App\Models\ReportFolder;
use App\Models\ReportTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_folders_tags_and_assign_them_to_reports(): void
    {
        $user = User::factory()->create();
        $report = $this->createReportFor($user);

        $folderResponse = $this->actingAs($user)->postJson('/api/v1/report-folders', [
            'name' => 'Cardiology studies',
        ]);

        $folderResponse->assertCreated()
            ->assertJsonPath('data.name', 'Cardiology studies');

        $folderId = $folderResponse->json('data.id');

        $tagResponse = $this->actingAs($user)->postJson('/api/v1/report-tags', [
            'name' => 'RCT',
        ]);

        $tagResponse->assertCreated()
            ->assertJsonPath('data.name', 'RCT');

        $tagId = $tagResponse->json('data.id');

        $this->actingAs($user)
            ->patchJson("/api/v1/reports/{$report->id}", [
                'report_folder_id' => $folderId,
                'tag_ids' => [$tagId],
            ])
            ->assertOk()
            ->assertJsonPath('data.report_folder_id', $folderId)
            ->assertJsonCount(1, 'data.tags');

        $this->actingAs($user)
            ->getJson("/api/v1/reports?report_folder_id={$folderId}")
            ->assertOk()
            ->assertJsonPath('data.0.id', $report->id);

        $this->actingAs($user)
            ->getJson('/api/v1/report-folders')
            ->assertOk()
            ->assertJsonPath('data.0.reports_count', 1);

        $this->actingAs($user)
            ->deleteJson("/api/v1/report-tags/{$tagId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('report_report_tag', [
            'report_id' => $report->id,
            'report_tag_id' => $tagId,
        ]);
    }

    private function createReportFor(User $user): Report
    {
        $dataFile = DataFile::factory()->create(['user_id' => $user->id]);

        $job = AnalysisJob::query()->create([
            'job_id' => 'DS-2026-LIBTEST01',
            'user_id' => $user->id,
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Analyze treatment outcomes across patient groups. ', 2),
            'status' => AnalysisJobStatus::Completed,
            'access_scope' => 'private',
            'payment_method' => 'pay_per_job',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);

        return Report::query()->create([
            'analysis_job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Library test report',
            'status' => ReportStatus::Published,
        ]);
    }
}
