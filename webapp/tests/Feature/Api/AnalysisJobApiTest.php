<?php

namespace Tests\Feature\Api;

use App\Enums\AnalysisAccessScope;
use App\Enums\AnalysisJobStatus;
use App\Enums\PaymentMethod;
use App\Enums\VirusScanStatus;
use App\Models\DataFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnalysisJobApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('filesystems.default', 'local');
        Config::set('doctorstats.payment_stub_enabled', true);
        Storage::fake('local');
        Http::fake([
            '*/api/v1/analyze' => Http::response(['analysis_id' => 'DS-TEST'], 202),
            '*' => Http::response(['message' => 'not implemented'], 404),
        ]);
    }

    public function test_user_can_create_analysis_job_for_validated_data_file(): void
    {
        $user = User::factory()->create();

        $dataFile = DataFile::factory()->create([
            'user_id' => $user->id,
            'virus_scan_status' => VirusScanStatus::Clean,
        ]);

        Storage::disk('local')->put(
            $dataFile->s3_path,
            "patient_id,age,group\n1,45,A\n2,52,B\n",
        );

        $objectives = str_repeat('Analyze treatment outcomes across patient groups. ', 2);

        $response = $this->actingAs($user)->postJson('/api/v1/analysis-jobs', [
            'data_file_id' => $dataFile->id,
            'objectives' => $objectives,
            'access_scope' => AnalysisAccessScope::Private->value,
            'payment_method' => PaymentMethod::PayPerJob->value,
            'columns' => [
                [
                    'column_name' => 'patient_id',
                    'column_index' => 0,
                    'data_type' => 'text',
                    'variable_type' => 'identifier',
                ],
                [
                    'column_name' => 'age',
                    'column_index' => 1,
                    'data_type' => 'numerical',
                    'variable_type' => 'independent',
                ],
                [
                    'column_name' => 'group',
                    'column_index' => 2,
                    'data_type' => 'categorical',
                    'variable_type' => 'dependent',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('job.status', AnalysisJobStatus::Processing->value)
            ->assertJsonPath('job.payment_method', PaymentMethod::PayPerJob->value)
            ->assertJsonCount(3, 'job.columns');

        $jobId = $response->json('job.job_id');

        $this->actingAs($user)
            ->getJson("/api/v1/analysis-jobs/{$jobId}/status")
            ->assertOk()
            ->assertJsonPath('data.status', AnalysisJobStatus::Processing->value);
    }

    public function test_analysis_job_requires_clean_data_file(): void
    {
        $user = User::factory()->create();

        $dataFile = DataFile::factory()->create([
            'user_id' => $user->id,
            'virus_scan_status' => VirusScanStatus::Pending,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/analysis-jobs', [
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Need at least fifty characters in objectives text. ', 2),
            'access_scope' => AnalysisAccessScope::Private->value,
            'payment_method' => PaymentMethod::PayPerJob->value,
            'columns' => [
                [
                    'column_name' => 'age',
                    'column_index' => 0,
                    'data_type' => 'numerical',
                    'variable_type' => 'dependent',
                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['data_file_id']);
    }
}
