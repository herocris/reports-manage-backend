<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates registration payloads for both web and mobile clients.
 */
class RegisterRequest extends FormRequest
{
    /**
     * Registration is publicly accessible.
     *
     * @return bool Always true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for user self-registration.
     *
     * Required: `name`, `email`, `password`, `confirmation_password`.
     * Optional: `institucion_id`, `device_name`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'institucion_id' => ['sometimes', 'nullable', 'integer', 'exists:institucions,id'],
            'device_name' => ['sometimes', 'string', 'max:255'],
            'roles' => ['prohibited'],
            'roles.*' => ['prohibited'],
        ];
    }
}
