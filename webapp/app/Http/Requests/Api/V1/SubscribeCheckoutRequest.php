<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_tier' => ['required', 'string', Rule::in(array_keys(config('doctorstats.subscription_plans')))],
            'billing_cycle' => ['required', Rule::in(['monthly', 'annual'])],
            'organization_id' => ['sometimes', 'nullable', 'integer', 'exists:organizations,id'],
        ];
    }
}
