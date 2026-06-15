<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreDataFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xls,xlsx,txt', 'max:51200'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
        ];
    }
}
