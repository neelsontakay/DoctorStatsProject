<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_file_id' => ['nullable', 'integer', 'exists:data_files,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
        ];
    }
}
