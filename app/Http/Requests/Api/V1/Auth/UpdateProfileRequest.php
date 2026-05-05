<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates the authenticated user's profile update input.
 *
 * Allows password changes only; name, email, roles, and institution are prohibited.
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * All authenticated users may update their own profile.
     * Ownership is implicitly guaranteed by Sanctum authentication.
     *
     * @return bool  Always true — any authenticated user can update their own profile.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the profile update payload.
     *
     * Only `password` may be changed via this endpoint; all other fields are
     * explicitly prohibited to prevent privilege escalation or accidental overwrites.
     * Password must satisfy the application's default password complexity rules.
     *
     * Required: `password` (string, meets `Password::defaults()`).
     * Prohibited: `name`, `email`, `institucion_id`, `roles`, `roles.*`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', Password::defaults()],
            'name' => ['prohibited'],
            'email' => ['prohibited'],
            'institucion_id' => ['prohibited'],
            'roles' => ['prohibited'],
            'roles.*' => ['prohibited'],
        ];
    }
}
