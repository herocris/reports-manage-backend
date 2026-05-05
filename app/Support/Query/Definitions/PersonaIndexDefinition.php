<?php

namespace App\Support\Query\Definitions;

use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Persona resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see \App\Support\Query\IndexQueryApplier}.
 */
class PersonaIndexDefinition
{
    /**
     * Return the full index configuration array for personas.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['nombre', 'numero_identificacion'],
            'sortable' => ['id', 'nombre', 'numero_identificacion', 'created_at', 'updated_at'],
            'includes' => ['adjuntos', 'informes'],
            'fields' => ['id', 'nombre', 'numero_identificacion', 'created_at', 'updated_at'],
            'filters' => [
                'nombre' => fn (Builder $query, string $value) => $query->where('nombre', 'like', '%'.$value.'%'),
                'numero_identificacion' => fn (Builder $query, string $value) => $query->where('numero_identificacion', 'like', '%'.$value.'%'),
                'informe_id' => fn (Builder $query, string $value) => $query->whereHas(
                    'informes',
                    fn (Builder $q) => $q->where('informes.id', $value)
                ),
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
