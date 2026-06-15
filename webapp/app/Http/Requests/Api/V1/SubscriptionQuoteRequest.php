<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionQuoteRequest extends FormRequest
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
        ];
    }
}
