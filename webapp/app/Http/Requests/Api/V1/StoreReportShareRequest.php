<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ShareMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'share_method' => ['required', Rule::enum(ShareMethod::class)],
            'recipient_email' => [
                Rule::requiredIf(fn () => $this->input('share_method') === ShareMethod::Email->value),
                'nullable',
                'email',
                'max:255',
            ],
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'organization_id' => [
                Rule::requiredIf(fn () => $this->input('share_method') === ShareMethod::OrgInternal->value),
                'nullable',
                'integer',
                'exists:organizations,id',
            ],
            'password' => ['nullable', 'string', 'min:6', 'max:128'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ];
    }
}
