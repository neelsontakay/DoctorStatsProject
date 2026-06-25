<?php

namespace App\Jobs;

use App\Models\DataFile;
use App\Services\StatsServiceClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProfileDataFileJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DataFile $dataFile,
    ) {}

    public function handle(StatsServiceClient $statsServiceClient): void
    {
        if (! $this->dataFile->isReadyForAnalysis()) {
            return;
        }

        try {
            $stats = $statsServiceClient->profileDataset($this->dataFile);

            if ($stats === null) {
                Log::warning('Dataset profiling returned no results.', [
                    'data_file_id' => $this->dataFile->id,
                ]);

                return;
            }

            $this->dataFile->update([
                'preview_stats' => $stats,
                'preview_stats_computed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Dataset profiling failed.', [
                'data_file_id' => $this->dataFile->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
