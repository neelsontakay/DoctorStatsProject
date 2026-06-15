<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UploadChunkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chunk_index' => ['required', 'integer', 'min:0'],
            'chunk' => ['required', 'file'],
        ];
    }
}
