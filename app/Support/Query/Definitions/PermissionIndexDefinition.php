<?php

namespace App\Support\Query\Definitions;

use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Permission resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see IndexQueryApplier}.
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
                'name' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('name', 'like', '%'.$value.'%'),
                ],
                'guard_name' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => 'guard_name',
                ],
                'role' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', $value)),
                ],
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
