<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InitUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filename' => ['required', 'string', 'max:255'],
            'total_size_bytes' => ['required', 'integer', 'min:1'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
        ];
    }
}
