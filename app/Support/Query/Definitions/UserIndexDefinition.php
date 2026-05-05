<?php

namespace App\Support\Query\Definitions;

use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the User resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see \App\Support\Query\IndexQueryApplier}.
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
            'fields' => ['id', 'name', 'email', 'email_verified_at', 'institucion_id', 'created_at', 'updated_at'],
            'filters' => [
                'name' => fn (Builder $query, string $value) => $query->where('name', 'like', '%'.$value.'%'),
                'email' => fn (Builder $query, string $value) => $query->where('email', 'like', '%'.$value.'%'),
                'institucion_id' => 'institucion_id',
                'role' => fn (Builder $query, string $value) => $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', $value)),
                'permission' => fn (Builder $query, string $value) => $query->whereHas('permissions', fn (Builder $permissionQuery) => $permissionQuery->where('name', $value)),
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
