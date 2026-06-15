<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\AnalysisAccessScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnalysisJobAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_scope' => ['required', Rule::enum(AnalysisAccessScope::class)],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
