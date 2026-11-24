<?php

namespace App\Http\Requests\Api\V1\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates input for creating a new permission.
 */
class StorePermissionRequest extends FormRequest
{
    /**
     * Grant access to all authenticated users; route middleware enforces the
     * `permission:permission.create` check before this request is instantiated.
     *
     * @return bool Always true — authorization is delegated to route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new permission.
     *
     * Required: `name` (string, max 255, unique in `permissions`).
     * Optional: `roles` (array of role name strings, each must exist in `roles.name` —
     * syncs the permission to those roles upon creation).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'sanctum')->whereNull('deleted_at')],
        ];
    }
}
