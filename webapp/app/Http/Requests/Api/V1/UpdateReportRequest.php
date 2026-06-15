<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'is_favourite' => ['sometimes', 'boolean'],
            'report_folder_id' => ['sometimes', 'nullable', 'integer', 'exists:report_folders,id'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:report_tags,id'],
        ];
    }
}
