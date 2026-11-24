<?php

namespace App\Http\Requests\Api\V1\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates input for creating a new role.
 */
class StoreRoleRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:role.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new role.
     *
     * Required: `name` (string, max 255, unique in `roles`).
     * Optional: `permissions` (array of permission name strings, each must exist in `permissions.name` —
     * syncs the role to those permissions upon creation).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'sanctum')->whereNull('deleted_at')],
        ];
    }
}
