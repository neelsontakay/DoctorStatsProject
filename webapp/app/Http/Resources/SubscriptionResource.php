<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Subscription */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $limit = config('doctorstats.subscription_analysis_limits')[$this->plan_tier] ?? null;

        return [
            'id' => $this->id,
            'plan_tier' => $this->plan_tier,
            'billing_cycle' => $this->billing_cycle,
            'status' => $this->status,
            'analyses_used_this_period' => $this->analyses_used_this_period,
            'analysis_limit' => $limit,
            'member_limit' => $this->member_limit,
            'auto_renew' => $this->auto_renew,
            'organization_id' => $this->organization_id,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
        ];
    }
}
