<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReportTagRequest;
use App\Http\Requests\Api\V1\UpdateReportTagRequest;
use App\Http\Resources\ReportTagResource;
use App\Models\Organization;
use App\Models\ReportTag;
use App\Services\ReportLibraryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReportTagController extends Controller
{
    public function __construct(
        private readonly ReportLibraryService $reportLibraryService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ReportTag::class);

        $organization = null;

        if ($request->filled('organization_id')) {
            $organization = Organization::query()->findOrFail($request->integer('organization_id'));
            $this->authorize('view', $organization);
        }

        $tags = $this->reportLibraryService
            ->tagsQuery($request->user(), $organization)
            ->withCount('reports')
            ->get();

        return ReportTagResource::collection($tags);
    }

    public function store(StoreReportTagRequest $request): ReportTagResource
    {
        $this->authorize('create', ReportTag::class);

        $organization = null;

        if ($request->filled('organization_id')) {
            $organization = Organization::query()->findOrFail($request->integer('organization_id'));
            $this->authorize('view', $organization);
        }

        $tag = $this->reportLibraryService->createTag(
            $request->user(),
            $request->validated(),
            $organization,
        );

        return new ReportTagResource($tag);
    }

    public function update(UpdateReportTagRequest $request, ReportTag $reportTag): ReportTagResource
    {
        $this->authorize('update', $reportTag);

        $tag = $this->reportLibraryService->updateTag($reportTag, $request->validated());

        return new ReportTagResource($tag);
    }

    public function destroy(ReportTag $reportTag): Response
    {
        $this->authorize('delete', $reportTag);

        $this->reportLibraryService->deleteTag($reportTag);

        return response()->noContent();
    }
}
