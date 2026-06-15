<?php

namespace App\Services;

use App\Models\AnalysisJob;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StatsServiceClient
{
    public function submitAnalysis(AnalysisJob $job): ?string
    {
        $job->loadMissing(['dataFile', 'columns']);

        $payload = [
            'job_id' => $job->job_id,
            'file_url' => $this->fileDownloadUrl($job),
            'file_format' => $job->dataFile->format->value,
            'sheet_name' => $job->dataFile->sheet_name,
            'objectives' => $job->objectives,
            'columns' => $job->columns->map(fn ($column) => [
                'name' => $column->column_name,
                'index' => $column->column_index,
                'data_type' => $column->data_type->value,
                'description' => $column->description,
                'unit' => $column->unit_of_measurement,
                'variable_type' => $column->variable_type->value,
            ])->values()->all(),
            'callback_url' => rtrim(config('app.url'), '/').'/internal/v1/analysis-callback',
        ];

        $response = $this->client()->post('/api/v1/analyze', $payload);

        if ($response->successful()) {
            return $response->json('analysis_id', $job->job_id);
        }

        Log::warning('Stats service analyze request failed.', [
            'job_id' => $job->job_id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function generateGraphs(AnalysisJob $job): array
    {
        $job->loadMissing(['dataFile', 'columns']);

        $payload = [
            'analysis_id' => $job->job_id,
            'file_url' => $this->fileDownloadUrl($job),
            'file_format' => $job->dataFile->format->value,
            'sheet_name' => $job->dataFile->sheet_name,
            'columns' => $job->columns->map(fn ($column) => [
                'name' => $column->column_name,
                'index' => $column->column_index,
                'data_type' => $column->data_type->value,
                'description' => $column->description,
                'unit' => $column->unit_of_measurement,
                'variable_type' => $column->variable_type->value,
            ])->values()->all(),
            'graph_types' => ['histogram', 'bar', 'box', 'scatter', 'heatmap'],
            'output_format' => 'png',
            'dpi' => 300,
        ];

        $response = $this->client()->post('/api/v1/generate-graphs', $payload);

        if (! $response->successful()) {
            Log::warning('Stats service graph generation failed.', [
                'job_id' => $job->job_id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        return $response->json('graphs', []);
    }

    private function fileDownloadUrl(AnalysisJob $job): string
    {
        $diskName = (string) config('filesystems.default');
        $disk = \Illuminate\Support\Facades\Storage::disk($diskName);

        if ($diskName === 's3') {
            return $disk->temporaryUrl($job->dataFile->s3_path, now()->addHour());
        }

        return rtrim((string) config('app.url'), '/')
            .'/internal/v1/data-files/'
            .$job->data_file_id
            .'/download';
    }

    private function client(): PendingRequest
    {
        $request = Http::baseUrl(rtrim(config('services.stats_service.url'), '/'))
            ->timeout((int) config('services.stats_service.timeout', 900))
            ->acceptJson();

        $token = config('services.stats_service.token');

        if (is_string($token) && trim($token) !== '') {
            $request = $request->withHeaders(['X-Service-Token' => trim($token)]);
        }

        return $request;
    }
}
