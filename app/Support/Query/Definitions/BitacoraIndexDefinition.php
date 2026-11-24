<?php

namespace App\Support\Query\Definitions;

use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Bitacora resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see IndexQueryApplier}.
 */
class BitacoraIndexDefinition
{
    /**
     * Return the full index configuration array for bitacoras.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['descripcion'],
            'sortable' => ['id', 'descripcion', 'fecha', 'informe_id', 'created_at', 'updated_at'],
            'includes' => ['informe'],
            'fields' => ['id', 'descripcion', 'fecha', 'informe_id', 'created_at', 'updated_at'],
            'filters' => [
                'descripcion' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('descripcion', 'like', '%'.$value.'%'),
                ],
                'informe_id' => [
                    'rules' => ['sometimes', 'integer', 'exists:informes,id'],
                    'query' => 'informe_id',
                ],
                'fecha' => [
                    'rules' => ['sometimes', 'date'],
                    'query' => 'fecha',
                ],
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
