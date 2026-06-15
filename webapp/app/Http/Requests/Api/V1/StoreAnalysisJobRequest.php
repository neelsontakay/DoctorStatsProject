<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\AnalysisAccessScope;
use App\Enums\ColumnDataType;
use App\Enums\ColumnVariableType;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnalysisJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_file_id' => ['required', 'integer', 'exists:data_files,id'],
            'objectives' => ['required', 'string', 'min:50'],
            'access_scope' => ['required', Rule::enum(AnalysisAccessScope::class)],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_id' => ['nullable', 'integer', 'exists:payments,id'],
            'subscription_id' => ['nullable', 'integer', 'exists:subscriptions,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'columns' => ['required', 'array', 'min:1'],
            'columns.*.column_name' => ['required', 'string', 'max:255'],
            'columns.*.column_index' => ['required', 'integer', 'min:0'],
            'columns.*.data_type' => ['required', Rule::enum(ColumnDataType::class)],
            'columns.*.description' => ['nullable', 'string', 'max:255'],
            'columns.*.unit_of_measurement' => ['nullable', 'string', 'max:64'],
            'columns.*.variable_type' => ['required', Rule::enum(ColumnVariableType::class)],
            'columns.*.quality_warnings' => ['nullable', 'array'],
        ];
    }
}
