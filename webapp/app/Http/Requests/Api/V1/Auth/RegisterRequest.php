<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'account_type' => ['required', Rule::enum(AccountType::class)],
            'organization_name' => [
                Rule::requiredIf(fn () => $this->input('account_type') === AccountType::Organizational->value),
                'nullable',
                'string',
                'max:255',
            ],
            'accepted_terms' => ['accepted'],
        ];
    }
}
