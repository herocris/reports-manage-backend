<?php

namespace App\Support\Query\Definitions;

use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;

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
                'numero' => 'numero',
                'titulo' => fn (Builder $query, string $value) => $query->where('titulo', 'like', '%'.$value.'%'),
                'descripcion' => fn (Builder $query, string $value) => $query->where('descripcion', 'like', '%'.$value.'%'),
                'estado' => 'estado',
                'usuario_id' => 'usuario_id',
                'informe_id' => 'informe_id',
                'cerrada_por_id' => 'cerrada_por_id',
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
