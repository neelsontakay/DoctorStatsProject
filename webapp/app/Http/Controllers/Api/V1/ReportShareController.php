<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReportShareRequest;
use App\Http\Resources\ReportShareResource;
use App\Models\Report;
use App\Models\ReportShare;
use App\Services\ReportShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportShareController extends Controller
{
    public function __construct(
        private readonly ReportShareService $reportShareService,
    ) {}

    public function index(Request $request, Report $report): AnonymousResourceCollection
    {
        $this->authorize('share', $report);

        $shares = $report->shares()->latest()->get();

        return ReportShareResource::collection($shares);
    }

    public function store(StoreReportShareRequest $request, Report $report): JsonResponse
    {
        $this->authorize('share', $report);

        $share = $this->reportShareService->create(
            $report,
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'message' => 'Report share created successfully.',
            'share' => new ReportShareResource($share),
        ], 201);
    }

    public function destroy(Request $request, Report $report, ReportShare $share): JsonResponse
    {
        $this->authorize('share', $report);

        $this->reportShareService->revoke($report, $share, $request->user());

        return response()->json([
            'message' => 'Report share revoked successfully.',
        ]);
    }
}
