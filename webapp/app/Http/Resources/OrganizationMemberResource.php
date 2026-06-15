<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrganizationMember */
class OrganizationMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'user_id' => $this->user_id,
            'role' => $this->role->value,
            'status' => $this->status->value,
            'joined_at' => $this->joined_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
        ];
    }
}
