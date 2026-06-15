<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'is_shared' => ['sometimes', 'boolean'],
        ];
    }
}
