<?php

namespace App\Jobs;

use App\Enums\VirusScanStatus;
use App\Models\DataFile;
use App\Services\SpreadsheetReaderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScanDataFileJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DataFile $dataFile,
    ) {}

    public function handle(SpreadsheetReaderService $spreadsheetReaderService): void
    {
        try {
            $spreadsheetReaderService->validateStructure($this->dataFile);

            $this->dataFile->update([
                'virus_scan_status' => VirusScanStatus::Clean,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Data file validation failed.', [
                'data_file_id' => $this->dataFile->id,
                'message' => $exception->getMessage(),
            ]);

            $this->dataFile->update([
                'virus_scan_status' => VirusScanStatus::Rejected,
            ]);
        }
    }
}
