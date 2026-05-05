<?php

namespace App\Support\Query\Definitions;

use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Permission resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see \App\Support\Query\IndexQueryApplier}.
 */
class PermissionIndexDefinition
{
    /**
     * Return the full index configuration array for permissions.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['name', 'guard_name'],
            'sortable' => ['id', 'name', 'guard_name', 'created_at', 'updated_at'],
            'includes' => ['roles'],
            'fields' => ['id', 'name', 'guard_name', 'created_at', 'updated_at'],
            'filters' => [
                'name' => fn (Builder $query, string $value) => $query->where('name', 'like', '%'.$value.'%'),
                'guard_name' => 'guard_name',
                'role' => fn (Builder $query, string $value) => $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', $value)),
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
