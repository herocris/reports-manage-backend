<?php

namespace App\Support\Query\Definitions;

use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

/**
 * Index query definition for the Observacion resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see IndexQueryApplier}.
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
            'searchable' => ['titulo', 'descripcion'],
            'sortable' => ['id', 'numero', 'titulo', 'descripcion', 'estado', 'usuario_id', 'informe_id', 'cerrada_at', 'created_at', 'updated_at'],
            'includes' => ['informe', 'usuario', 'cerradaPor', 'comentarios', 'comentarios.usuario'],
            'fields' => ['id', 'numero', 'titulo', 'descripcion', 'estado', 'usuario_id', 'informe_id', 'cerrada_at', 'cerrada_por_id', 'created_at', 'updated_at'],
            'filters' => [
                'numero' => [
                    'rules' => ['sometimes', 'integer', 'min:1'],
                    'query' => 'numero',
                ],
                'titulo' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('titulo', 'like', '%'.$value.'%'),
                ],
                'descripcion' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('descripcion', 'like', '%'.$value.'%'),
                ],
                'estado' => [
                    'rules' => ['sometimes', 'string', Rule::in(['abierta', 'cerrada'])],
                    'query' => 'estado',
                ],
                'usuario_id' => [
                    'rules' => ['sometimes', 'integer'],
                    'query' => 'usuario_id',
                ],
                'informe_id' => [
                    'rules' => ['sometimes', 'integer'],
                    'query' => 'informe_id',
                ],
                'cerrada_por_id' => [
                    'rules' => ['sometimes', 'integer'],
                    'query' => 'cerrada_por_id',
                ],
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
