<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Http\Requests\Api\V1\UpdateReportRequest;
use App\Services\AnalysisJobService;
use App\Services\ReportExportService;
use App\Services\ReportLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly AnalysisJobService $analysisJobService,
        private readonly ReportExportService $reportExportService,
        private readonly ReportLibraryService $reportLibraryService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Report::class);

        $jobIds = $this->analysisJobService
            ->accessibleJobsQuery($request->user())
            ->pluck('id');

        $reports = Report::query()
            ->with(['analysisJob', 'folder', 'tags'])
            ->whereIn('analysis_job_id', $jobIds)
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->boolean('favourites'), fn ($query) => $query->where('is_favourite', true))
            ->when($request->filled('report_folder_id'), fn ($query) => $query->where('report_folder_id', $request->integer('report_folder_id')))
            ->when($request->filled('tag_id'), fn ($query) => $query->whereHas('tags', fn ($tagQuery) => $tagQuery->where('report_tags.id', $request->integer('tag_id'))))
            ->latest()
            ->paginate(20);

        return ReportResource::collection($reports);
    }

    public function show(Request $request, Report $report): ReportResource
    {
        $this->authorize('view', $report);

        return new ReportResource($report->load(['analysisJob', 'folder', 'tags']));
    }

    public function view(Request $request, Report $report): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $report);

        if ($report->web_html_path === null) {
            return response()->json([
                'message' => 'Report HTML is not available yet.',
            ], 404);
        }

        $disk = Storage::disk(config('filesystems.default'));

        if (! $disk->exists($report->web_html_path)) {
            return response()->json([
                'message' => 'Report file could not be found in storage.',
            ], 404);
        }

        return $disk->response($report->web_html_path, 'report.html', [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function download(Request $request, Report $report, string $format): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $report);

        try {
            $report->loadMissing('analysisJob');
            $path = $this->reportExportService->resolveDownloadPath($report, $format);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        $disk = Storage::disk(config('filesystems.default'));

        $filename = match ($format) {
            'pdf' => 'report.pdf',
            'excel' => 'report.xlsx',
            default => 'report.html',
        };

        $contentType = match ($format) {
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'text/html; charset=UTF-8',
        };

        return $disk->response($path, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    public function update(UpdateReportRequest $request, Report $report): ReportResource
    {
        $this->authorize('update', $report);

        $validated = $request->validated();

        $report->update(collect($validated)->only(['title', 'is_favourite'])->all());

        $report = $this->reportLibraryService->assignReportMetadata(
            $report,
            array_key_exists('report_folder_id', $validated) ? $validated['report_folder_id'] : false,
            array_key_exists('tag_ids', $validated) ? $validated['tag_ids'] : false,
        );

        return new ReportResource($report);
    }
}
