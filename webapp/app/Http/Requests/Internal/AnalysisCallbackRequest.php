<?php

namespace App\Http\Requests\Internal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalysisCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_id' => ['required', 'string', 'exists:analysis_jobs,job_id'],
            'status' => ['required', Rule::in(['processing', 'completed', 'failed'])],
            'progress_percent' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'current_step' => ['nullable', 'string', 'max:255'],
            'error' => ['nullable', 'string'],
            'data_profile' => ['nullable', 'array'],
            'tests' => ['nullable', 'array'],
            'tests.*.test_name' => ['required_with:tests', 'string', 'max:255'],
            'tests.*.test_category' => ['required_with:tests', 'string', 'max:64'],
            'tests.*.parameters' => ['nullable', 'array'],
            'tests.*.test_statistic' => ['nullable', 'numeric'],
            'tests.*.p_value' => ['nullable', 'numeric'],
            'tests.*.confidence_intervals' => ['nullable', 'array'],
            'tests.*.effect_sizes' => ['nullable', 'array'],
            'tests.*.assumptions_validation' => ['nullable', 'array'],
            'tests.*.raw_output' => ['nullable', 'array'],
        ];
    }
}
