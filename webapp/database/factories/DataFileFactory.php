<?php

namespace Database\Factories;

use App\Enums\DataFileFormat;
use App\Enums\VirusScanStatus;
use App\Models\DataFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataFile>
 */
class DataFileFactory extends Factory
{
    protected $model = DataFile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organization_id' => null,
            'original_filename' => 'dataset.csv',
            's3_path' => 'data-files/1/example.csv',
            'format' => DataFileFormat::Csv,
            'file_size_bytes' => 1024,
            'sheet_name' => null,
            'virus_scan_status' => VirusScanStatus::Clean,
        ];
    }
}
