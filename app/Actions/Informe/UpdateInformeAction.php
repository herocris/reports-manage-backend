<?php

namespace App\Actions\Informe;

use App\Models\Informe;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateInformeAction
{
    /**
     * @param  array{expediente?: string, titulo?: string, producto_id?: int, fecha_inicio?: string, fecha_final?: string, ambitos?: array<int, int>}  $attributes
     */
    public function execute(Informe $informe, array $attributes): Informe
    {
        return DB::transaction(function () use ($informe, $attributes): Informe {
            $informe = Informe::query()->lockForUpdate()->findOrFail($informe->id);
            $informe->update(Arr::except($attributes, ['ambitos']));

            if (array_key_exists('ambitos', $attributes)) {
                $informe->ambitos()->sync($attributes['ambitos']);
            }

            return $informe;
        }, attempts: 3);
    }
}
