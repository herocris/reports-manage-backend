<?php

namespace App\Support\Query\Definitions;

use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Observacion resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see \App\Support\Query\IndexQueryApplier}.
 */
class ObservacionIndexDefinition
{
    /**
     * Return the full index configuration array for observaciones.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['descripcion'],
            'sortable' => ['id', 'descripcion', 'usuario_id', 'informe_id', 'created_at', 'updated_at'],
            'includes' => ['informe'],
            'fields' => ['id', 'descripcion', 'usuario_id', 'informe_id', 'created_at', 'updated_at'],
            'filters' => [
                'descripcion' => fn (Builder $query, string $value) => $query->where('descripcion', 'like', '%'.$value.'%'),
                'usuario_id' => 'usuario_id',
                'informe_id' => 'informe_id',
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
