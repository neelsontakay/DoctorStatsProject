<?php

namespace Tests\Feature\Api;

use App\Enums\VirusScanStatus;
use App\Models\DataFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataFileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('filesystems.default', 'local');
        Storage::fake('local');
    }

    public function test_user_can_upload_csv_file_and_preview_rows(): void
    {
        $user = User::factory()->create();

        $csv = UploadedFile::fake()->createWithContent(
            'patients.csv',
            "patient_id,age,group\n1,45,A\n2,52,B\n",
        );

        $uploadResponse = $this->actingAs($user)
            ->post('/api/v1/data-files', ['file' => $csv]);

        $uploadResponse->assertCreated()
            ->assertJsonPath('data_file.original_filename', 'patients.csv')
            ->assertJsonPath('data_file.virus_scan_status', VirusScanStatus::Clean->value);

        $dataFile = DataFile::query()->firstOrFail();
        $this->assertTrue(Storage::disk('local')->exists($dataFile->s3_path));

        $previewResponse = $this->actingAs($user)
            ->getJson("/api/v1/data-files/{$dataFile->id}/preview");

        $previewResponse->assertOk()
            ->assertJsonPath('data.headers', ['patient_id', 'age', 'group'])
            ->assertJsonCount(2, 'data.rows');
    }

    public function test_user_cannot_preview_another_users_file(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $dataFile = DataFile::factory()->create([
            'user_id' => $owner->id,
            'virus_scan_status' => VirusScanStatus::Clean,
        ]);

        Storage::disk('local')->put(
            $dataFile->s3_path,
            "patient_id,age\n1,45\n",
        );

        $this->actingAs($other)
            ->getJson("/api/v1/data-files/{$dataFile->id}/preview")
            ->assertForbidden();
    }
}
