<?php

namespace App\Support\Query\Definitions;

use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Role resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see \App\Support\Query\IndexQueryApplier}.
 */
class RoleIndexDefinition
{
    /**
     * Return the full index configuration array for roles.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['name', 'guard_name'],
            'sortable' => ['id', 'name', 'guard_name', 'created_at', 'updated_at'],
            'includes' => ['permissions'],
            'fields' => ['id', 'name', 'guard_name', 'created_at', 'updated_at'],
            'filters' => [
                'name' => fn (Builder $query, string $value) => $query->where('name', 'like', '%'.$value.'%'),
                'guard_name' => 'guard_name',
                'permission' => fn (Builder $query, string $value) => $query->whereHas('permissions', fn (Builder $permissionQuery) => $permissionQuery->where('name', $value)),
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
