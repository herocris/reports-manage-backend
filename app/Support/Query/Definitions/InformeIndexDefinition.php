<?php

namespace App\Support\Query\Definitions;

use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Informe resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see \App\Support\Query\IndexQueryApplier}.
 */
class InformeIndexDefinition
{
    /**
     * Return the full index configuration array for informes.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['expediente', 'titulo'],
            'sortable' => ['id', 'expediente', 'titulo', 'producto_id', 'fecha_inicio', 'fecha_final', 'created_at', 'updated_at'],
            'includes' => ['producto', 'personas', 'bienes', 'adjuntos', 'ubicaciones', 'observaciones', 'bitacoras', 'ambitos', 'usuarioCreador'],
            'fields' => ['id', 'expediente', 'titulo', 'producto_id', 'fecha_inicio', 'fecha_final', 'usuariocreador_id', 'created_at', 'updated_at'],
            'filters' => [
                'expediente' => fn (Builder $query, string $value) => $query->where('expediente', 'like', '%'.$value.'%'),
                'titulo' => fn (Builder $query, string $value) => $query->where('titulo', 'like', '%'.$value.'%'),                
                'producto' => fn (Builder $query, string $value) => $query->whereHas('producto', fn (Builder $productQuery) => $productQuery->where('nombre', 'like', '%'.$value.'%')),
                'fecha_inicio' => 'fecha_inicio',
                'fecha_final' => 'fecha_final',
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
