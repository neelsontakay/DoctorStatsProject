<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportShare */
class ReportShareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_id' => $this->report_id,
            'share_method' => $this->share_method->value,
            'token' => $this->when($this->token !== null, $this->token),
            'share_url' => $this->when($this->token !== null, fn () => url('/shared/'.$this->token)),
            'recipient_email' => $this->recipient_email,
            'recipient_user_id' => $this->recipient_user_id,
            'organization_id' => $this->organization_id,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'has_password' => $this->password_hash !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
