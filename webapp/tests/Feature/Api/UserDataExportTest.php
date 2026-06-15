<?php

namespace Tests\Feature\Api;

use App\Enums\AnalysisJobStatus;
use App\Models\AnalysisJob;
use App\Models\DataFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDataExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_personal_data(): void
    {
        $user = User::factory()->create();
        $dataFile = DataFile::factory()->create(['user_id' => $user->id]);

        AnalysisJob::query()->create([
            'job_id' => 'DS-2026-EXPORT-USER',
            'user_id' => $user->id,
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Analyze treatment outcomes across patient groups. ', 2),
            'status' => AnalysisJobStatus::Completed,
            'access_scope' => 'private',
            'payment_method' => 'pay_per_job',
            'submitted_at' => now(),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/me/export');

        $response->assertOk()
            ->assertJsonPath('profile.email', $user->email)
            ->assertJsonPath('analysis_jobs.0.job_id', 'DS-2026-EXPORT-USER')
            ->assertJsonStructure([
                'exported_at',
                'profile',
                'organization_memberships',
                'analysis_jobs',
                'subscriptions',
                'payments',
                'notifications',
                'audit_logs',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'user.data_exported',
        ]);
    }
}
