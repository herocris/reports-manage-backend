<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Validates input for updating an existing user account.
 *
 * Includes optional role re-synchronization.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:user.update` check before this request is instantiated.
     *
     * @return bool  Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating an existing user account.
     *
     * All fields are optional (`sometimes`) to support partial updates (PATCH).
     * Keys: `name` (string, max 255), `email` (unique ignoring self), `password` (meets defaults),
     * `institucion_id` (integer, must exist in `institucions`),
     * `roles` (array of role name strings — full sync, replaces all current roles).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['sometimes', 'string', Password::defaults()],
            'institucion_id' => ['sometimes', 'integer', 'exists:institucions,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
