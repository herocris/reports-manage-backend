<?php

namespace App\Support\Query\Definitions;

use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Institucion resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see IndexQueryApplier}.
 */
class InstitucionIndexDefinition
{
    /**
     * Return the full index configuration array for instituciones.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['nombre', 'contacto', 'telefono'],
            'sortable' => ['id', 'nombre', 'contacto', 'telefono', 'created_at', 'updated_at'],
            'includes' => [],
            'fields' => ['id', 'nombre', 'contacto', 'telefono', 'created_at', 'updated_at'],
            'filters' => [
                'nombre' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('nombre', 'like', '%'.$value.'%'),
                ],
                'contacto' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('contacto', 'like', '%'.$value.'%'),
                ],
                'telefono' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('telefono', 'like', '%'.$value.'%'),
                ],
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
