<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'account_type' => $this->account_type->value,
            'status' => $this->status->value,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'organization_memberships' => OrganizationMemberResource::collection(
                $this->whenLoaded('organizationMemberships'),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
