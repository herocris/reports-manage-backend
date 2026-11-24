<?php

namespace App\Support\Query\Definitions;

use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the User resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see IndexQueryApplier}.
 */
class UserIndexDefinition
{
    /**
     * Return the full index configuration array for users.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['name', 'email'],
            'sortable' => ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'],
            'includes' => ['roles', 'permissions', 'institucion'],
            'include_loads' => [
                'roles' => ['roles.permissions', 'permissions'],
                'permissions' => ['roles.permissions', 'permissions'],
            ],
            'fields' => ['id', 'name', 'email', 'email_verified_at', 'institucion_id', 'created_at', 'updated_at'],
            'filters' => [
                'name' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('name', 'like', '%'.$value.'%'),
                ],
                'email' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('email', 'like', '%'.$value.'%'),
                ],
                'institucion_id' => [
                    'rules' => ['sometimes', 'integer', 'exists:institucions,id'],
                    'query' => 'institucion_id',
                ],
                'role' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', $value)),
                ],
                'permission' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->whereHas('permissions', fn (Builder $permissionQuery) => $permissionQuery->where('name', $value)),
                ],
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
