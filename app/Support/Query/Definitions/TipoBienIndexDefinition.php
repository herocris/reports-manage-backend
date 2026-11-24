<?php

namespace App\Support\Query\Definitions;

use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the TipoBien resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see IndexQueryApplier}.
 */
class TipoBienIndexDefinition
{
    /**
     * Return the full index configuration array for tipos de bien.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['nombre', 'descripcion'],
            'sortable' => ['id', 'nombre', 'descripcion', 'created_at', 'updated_at'],
            'includes' => ['bienes'],
            'fields' => ['id', 'nombre', 'descripcion', 'created_at', 'updated_at'],
            'filters' => [
                'nombre' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('nombre', 'like', '%'.$value.'%'),
                ],
                'descripcion' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('descripcion', 'like', '%'.$value.'%'),
                ],
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
