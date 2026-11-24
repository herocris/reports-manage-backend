<?php

namespace App\Actions\Informe;

use App\Models\Informe;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateInformeAction
{
    /**
     * @param  array{expediente: string, titulo: string, producto_id: int, fecha_inicio: string, fecha_final: string, ambitos?: array<int, int>}  $attributes
     */
    public function execute(array $attributes, User $author): Informe
    {
        return DB::transaction(function () use ($attributes, $author): Informe {
            $informe = Informe::query()->create([
                ...Arr::except($attributes, ['ambitos']),
                'usuariocreador_id' => $author->id,
            ]);

            if (array_key_exists('ambitos', $attributes)) {
                $informe->ambitos()->sync($attributes['ambitos']);
            }

            return $informe;
        }, attempts: 3);
    }
}
