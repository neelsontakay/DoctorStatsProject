<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Models\AnalysisJob;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(
        private readonly StatsServiceClient $statsServiceClient,
        private readonly AiInterpretationService $aiInterpretationService,
        private readonly ReportAssemblyService $reportAssemblyService,
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {}

    public function generate(AnalysisJob $job): Report
    {
        return DB::transaction(function () use ($job): Report {
            $job->loadMissing(['columns', 'results', 'dataFile', 'user', 'organization']);

            $graphs = $this->statsServiceClient->generateGraphs($job);
            $storedGraphs = $this->storeGraphs($job, $graphs);

            $aiContent = $this->aiInterpretationService->interpret($job);
            $html = $this->reportAssemblyService->renderHtml($job, $aiContent, $storedGraphs);
            $htmlPath = $this->storeHtml($job, $html);

            $interpretation = trim(implode("\n\n", array_filter([
                $aiContent['interpretation'] ?? '',
                isset($aiContent['limitations']) ? "Limitations:\n".$aiContent['limitations'] : null,
                isset($aiContent['recommendations']) ? "Recommendations:\n".$aiContent['recommendations'] : null,
            ])));

            $report = Report::query()->updateOrCreate(
                ['analysis_job_id' => $job->id],
                [
                    'user_id' => $job->user_id,
                    'organization_id' => $job->organization_id,
                    'title' => $this->buildTitle($job),
                    'executive_summary' => $aiContent['executive_summary'] ?? null,
                    'ai_interpretation' => $interpretation !== '' ? $interpretation : null,
                    'web_html_path' => $htmlPath,
                    'status' => ReportStatus::Published,
                ],
            );

            $this->auditLogService->record(
                'report.generated',
                $job->user,
                $job->organization,
                $report,
                [
                    'job_id' => $job->job_id,
                    'ai_provider' => $aiContent['provider'] ?? 'unknown',
                ],
            );

            $this->notificationService->analysisCompleted($job, $report);

            return $report->fresh(['analysisJob']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $graphs
     * @return list<array<string, mixed>>
     */
    private function storeGraphs(AnalysisJob $job, array $graphs): array
    {
        $disk = Storage::disk(config('filesystems.default'));
        $stored = [];

        foreach ($graphs as $index => $graph) {
            $imageBase64 = $graph['image_base64'] ?? null;

            if (! is_string($imageBase64) || $imageBase64 === '') {
                continue;
            }

            $path = sprintf(
                'reports/%s/graphs/%s_%d.png',
                $job->job_id,
                $graph['graph_type'] ?? 'graph',
                $index,
            );

            $disk->put($path, base64_decode($imageBase64));

            $stored[] = [
                'graph_type' => $graph['graph_type'] ?? 'graph',
                'title' => $graph['title'] ?? 'Visualization',
                'caption' => $graph['caption'] ?? '',
                'storage_path' => $path,
                'mime_type' => $graph['mime_type'] ?? 'image/png',
                'image_base64' => $imageBase64,
            ];
        }

        return $stored;
    }

    private function storeHtml(AnalysisJob $job, string $html): string
    {
        $path = sprintf('reports/%s/report.html', $job->job_id);
        Storage::disk(config('filesystems.default'))->put($path, $html);

        return $path;
    }

    private function buildTitle(AnalysisJob $job): string
    {
        $snippet = Str::limit($job->objectives, 80);

        return "Analysis Report — {$job->job_id} — {$snippet}";
    }
}
