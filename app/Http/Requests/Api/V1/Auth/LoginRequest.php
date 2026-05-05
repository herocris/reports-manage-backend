<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates login credentials for both web (session) and mobile (token) flows.
 *
 * Accepts an optional `device_name` to label the issued access token.
 */
class LoginRequest extends FormRequest
{
    /**
     * All unauthenticated users are allowed to submit login credentials.
     * There is no permission middleware on the login route.
     *
     * @return bool  Always true — the login endpoint is publicly accessible.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for authentication credentials.
     *
     * Required: `email` (valid email address), `password` (string).
     * Optional: `device_name` (string, max 255 — used as the Sanctum token label
     * for mobile bearer-token issuance; defaults to a generic label if omitted).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
