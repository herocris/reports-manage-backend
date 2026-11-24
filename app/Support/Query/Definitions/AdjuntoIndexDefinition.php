<?php

namespace App\Support\Query\Definitions;

use App\Models\Bien;
use App\Models\Informe;
use App\Models\Persona;
use App\Support\Query\IndexQueryApplier;
use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the Adjunto resource.
 *
 * Configures search, sort, filters, and allowed includes
 * used by {@see IndexQueryApplier}.
 */
class AdjuntoIndexDefinition
{
    /**
     * Return the full index configuration array for adjuntos.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['descripcion', 'url'],
            'sortable' => ['id', 'descripcion', 'adjuntable_id', 'created_at', 'updated_at'],
            'includes' => [],
            'fields' => ['id', 'url', 'descripcion', 'adjuntable_id', 'adjuntable_type', 'created_at', 'updated_at'],
            'filters' => [
                'descripcion' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => fn (Builder $query, string $value) => $query->where('descripcion', 'like', '%'.$value.'%'),
                ],
                'informe_id' => [
                    'rules' => ['sometimes', 'integer', 'exists:informes,id'],
                    'query' => fn (Builder $query, string $value) => $query->whereHasMorph(
                        'adjuntable',
                        [Informe::class],
                        fn (Builder $adjuntableQuery) => $adjuntableQuery->where('informes.id', $value)
                    ),
                ],
                'persona_id' => [
                    'rules' => ['sometimes', 'integer', 'exists:personas,id'],
                    'query' => fn (Builder $query, string $value) => $query->whereHasMorph(
                        'adjuntable',
                        [Persona::class],
                        fn (Builder $adjuntableQuery) => $adjuntableQuery->where('personas.id', $value)
                    ),
                ],
                'bien_id' => [
                    'rules' => ['sometimes', 'integer', 'exists:biens,id'],
                    'query' => fn (Builder $query, string $value) => $query->whereHasMorph(
                        'adjuntable',
                        [Bien::class],
                        fn (Builder $adjuntableQuery) => $adjuntableQuery->where('biens.id', $value)
                    ),
                ],
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 15,
        ];
    }
}
