<?php

namespace App\Actions\Observacion;

use App\Enums\ObservacionEstado;
use App\Models\Observacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CloseObservacionAction
{
    public function execute(Observacion $observacion, User $responsable, string $contenido): Observacion
    {
        return DB::transaction(function () use ($observacion, $responsable, $contenido): Observacion {
            $observacion = Observacion::query()->lockForUpdate()->findOrFail($observacion->id);

            if ($observacion->estado === ObservacionEstado::Cerrada) {
                throw ValidationException::withMessages([
                    'estado' => ['La observación ya está cerrada.'],
                ]);
            }

            $observacion->comentarios()->create([
                'usuario_id' => $responsable->id,
                'contenido' => $contenido,
            ]);

            $observacion->update([
                'estado' => ObservacionEstado::Cerrada,
                'cerrada_at' => now(),
                'cerrada_por_id' => $responsable->id,
            ]);

            return $observacion->load(['usuario', 'cerradaPor', 'comentarios.usuario']);
        }, attempts: 3);
    }
}
