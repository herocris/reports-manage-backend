<?php

namespace App\Support\Query\Definitions;

use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Ambito resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see \App\Support\Query\IndexQueryApplier}.
 */
class AmbitoIndexDefinition
{
    /**
     * Return the full index configuration array for ambitos.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['nombre', 'descripcion'],
            'sortable' => ['id', 'nombre', 'descripcion', 'created_at', 'updated_at'],
            'includes' => ['informes'],
            'fields' => ['id', 'nombre', 'descripcion', 'created_at', 'updated_at'],
            'filters' => [
                'nombre' => fn (Builder $query, string $value) => $query->where('nombre', 'like', '%'.$value.'%'),
                'descripcion' => fn (Builder $query, string $value) => $query->where('descripcion', 'like', '%'.$value.'%'),
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
