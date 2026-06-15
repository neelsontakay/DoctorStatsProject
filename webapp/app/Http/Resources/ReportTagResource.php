<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportTag */
class ReportTagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'organization_id' => $this->organization_id,
            'reports_count' => $this->whenCounted('reports'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
