<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AnalysisJob */
class AnalysisJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->job_id,
            'objectives' => $this->objectives,
            'status' => $this->status->value,
            'access_scope' => $this->access_scope->value,
            'payment_method' => $this->payment_method->value,
            'organization_id' => $this->organization_id,
            'data_file_id' => $this->data_file_id,
            'payment_id' => $this->payment_id,
            'subscription_id' => $this->subscription_id,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'data_file' => new DataFileResource($this->whenLoaded('dataFile')),
            'columns' => AnalysisColumnResource::collection($this->whenLoaded('columns')),
            'member_ids' => $this->whenLoaded(
                'jobMembers',
                fn () => $this->jobMembers->pluck('user_id')->values(),
            ),
        ];
    }
}
