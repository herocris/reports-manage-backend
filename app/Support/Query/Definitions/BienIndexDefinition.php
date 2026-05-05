<?php

namespace App\Support\Query\Definitions;

use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Bien resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see \App\Support\Query\IndexQueryApplier}.
 */
class BienIndexDefinition
{
    /**
     * Return the full index configuration array for bienes.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['descripcion'],
            'sortable' => ['id', 'descripcion', 'tipobien_id', 'created_at', 'updated_at'],
            'includes' => ['tipoBien', 'adjuntos'],
            'fields' => ['id', 'descripcion', 'tipobien_id', 'created_at', 'updated_at'],
            'filters' => [
                'descripcion' => fn (Builder $query, string $value) => $query->where('descripcion', 'like', '%'.$value.'%'),
                'tipoBien' => fn (Builder $query, string $value) => $query->whereHas('tipoBien', fn (Builder $tipoBienQuery) => $tipoBienQuery->where('nombre', 'like', '%'.$value.'%')),
                'informe_id' => fn (Builder $query, string $value) => $query->whereHas(
                    'informes',
                    fn (Builder $informeQuery) => $informeQuery->where('informes.id', $value)
                ),
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
