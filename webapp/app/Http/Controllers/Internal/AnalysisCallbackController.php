<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Internal\AnalysisCallbackRequest;
use App\Services\AnalysisCallbackService;
use Illuminate\Http\JsonResponse;

class AnalysisCallbackController extends Controller
{
    public function __construct(
        private readonly AnalysisCallbackService $analysisCallbackService,
    ) {}

    public function store(AnalysisCallbackRequest $request): JsonResponse
    {
        $job = $this->analysisCallbackService->handle($request->validated());

        return response()->json([
            'message' => 'Callback processed successfully.',
            'job_id' => $job->job_id,
            'status' => $job->status->value,
        ]);
    }
}
