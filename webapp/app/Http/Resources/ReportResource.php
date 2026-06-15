<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Report */
class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'analysis_job_id' => $this->analysis_job_id,
            'job_id' => $this->whenLoaded('analysisJob', fn () => $this->analysisJob->job_id),
            'title' => $this->title,
            'executive_summary' => $this->executive_summary,
            'ai_interpretation' => $this->ai_interpretation,
            'web_html_path' => $this->web_html_path,
            'status' => $this->status->value,
            'is_favourite' => $this->is_favourite,
            'report_folder_id' => $this->report_folder_id,
            'folder' => $this->whenLoaded('folder', fn () => new ReportFolderResource($this->folder)),
            'tags' => ReportTagResource::collection($this->whenLoaded('tags')),
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
