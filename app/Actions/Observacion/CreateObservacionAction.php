<?php

namespace App\Actions\Observacion;

use App\Models\Informe;
use App\Models\Observacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateObservacionAction
{
    /** @param array{titulo: string, descripcion: string, informe_id: int} $attributes */
    public function execute(array $attributes, User $author): Observacion
    {
        return DB::transaction(function () use ($attributes, $author): Observacion {
            $informe = Informe::query()->lockForUpdate()->findOrFail($attributes['informe_id']);
            $numero = $informe->observaciones()->withTrashed()->max('numero') + 1;

            return $informe->observaciones()->create([
                'numero' => $numero,
                'titulo' => $attributes['titulo'],
                'descripcion' => $attributes['descripcion'],
                'usuario_id' => $author->id,
            ]);
        }, attempts: 3);
    }
}
