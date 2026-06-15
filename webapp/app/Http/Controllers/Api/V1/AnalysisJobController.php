<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AnalysisAccessScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAnalysisJobRequest;
use App\Http\Requests\Api\V1\UpdateAnalysisJobAccessRequest;
use App\Http\Resources\AnalysisJobResource;
use App\Http\Resources\AnalysisResultResource;
use App\Models\AnalysisJob;
use App\Services\AnalysisJobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnalysisJobController extends Controller
{
    public function __construct(
        private readonly AnalysisJobService $analysisJobService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AnalysisJob::class);

        $jobs = $this->analysisJobService
            ->accessibleJobsQuery($request->user(), $request->query('status'))
            ->with(['dataFile', 'columns', 'jobMembers', 'organization'])
            ->latest()
            ->paginate(20);

        return AnalysisJobResource::collection($jobs);
    }

    public function store(StoreAnalysisJobRequest $request): JsonResponse
    {
        $this->authorize('create', AnalysisJob::class);

        $job = $this->analysisJobService->submit(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'message' => 'Analysis job created successfully.',
            'job' => new AnalysisJobResource($job),
        ], 201);
    }

    public function show(Request $request, AnalysisJob $analysisJob): AnalysisJobResource
    {
        $this->authorize('view', $analysisJob);

        $analysisJob->load(['dataFile', 'columns', 'jobMembers', 'organization']);

        return new AnalysisJobResource($analysisJob);
    }

    public function results(Request $request, AnalysisJob $analysisJob): JsonResponse
    {
        $this->authorize('view', $analysisJob);

        $results = $analysisJob->results()->orderBy('id')->get();

        return response()->json([
            'data' => [
                'job_id' => $analysisJob->job_id,
                'status' => $analysisJob->status->value,
                'results' => AnalysisResultResource::collection($results),
            ],
        ]);
    }

    public function status(Request $request, AnalysisJob $analysisJob): JsonResponse
    {
        $this->authorize('view', $analysisJob);

        return response()->json([
            'data' => [
                'job_id' => $analysisJob->job_id,
                'status' => $analysisJob->status->value,
                'submitted_at' => $analysisJob->submitted_at?->toIso8601String(),
                'completed_at' => $analysisJob->completed_at?->toIso8601String(),
            ],
        ]);
    }

    public function updateAccess(
        UpdateAnalysisJobAccessRequest $request,
        AnalysisJob $analysisJob,
    ): AnalysisJobResource {
        $this->authorize('updateAccess', $analysisJob);

        $job = $this->analysisJobService->updateAccess(
            $analysisJob,
            $request->user(),
            AnalysisAccessScope::from($request->validated('access_scope')),
            $request->validated('member_ids', []),
        );

        return new AnalysisJobResource($job->load(['dataFile', 'columns', 'jobMembers']));
    }
}
