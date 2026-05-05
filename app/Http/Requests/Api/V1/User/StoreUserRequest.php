<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates input for creating a new user account.
 *
 * Includes optional role assignment.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:user.create` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new user account.
     *
     * Required: `name` (string, max 255), `email` (valid email, unique in `users`),
     * `password` (string, meets `Password::defaults()`),
     * `institucion_id` (integer, must exist in `institucions`).
     * Optional: `roles` (array of role name strings — synced upon creation).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'institucion_id' => ['required', 'integer', 'exists:institucions,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
