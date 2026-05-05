<?php

namespace Database\Seeders;

use App\Models\Adjunto;
use App\Models\Ambito;
use App\Models\Bien;
use App\Models\Bitacora;
use App\Models\Informe;
use App\Models\Observacion;
use App\Models\Persona;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds informe records and wires up their many-to-many associations
 * (personas, bienes, ambitos) and child records
 * (ubicaciones, observaciones, bitacoras, adjuntos).
 */
class InformeSeeder extends Seeder
{
    public function run(): void
    {
        $personas = Persona::all();
        $bienes = Bien::all();
        $ambitos = Ambito::all();
        $users = User::all();

        Informe::factory()->count(10)->create()->each(function (Informe $informe) use ($personas, $bienes, $ambitos, $users): void {
            $informe->personas()->attach($personas->random(rand(1, 4))->pluck('id'));
            $informe->bienes()->attach($bienes->random(rand(1, 3))->pluck('id'));
            $informe->ambitos()->attach($ambitos->random(rand(1, 3))->pluck('id'));

            Adjunto::factory()->count(rand(1, 4))->create([
                'adjuntable_id' => $informe->id,
                'adjuntable_type' => Informe::class,
            ]);

            Observacion::factory()->count(rand(1, 5))->create([
                'informe_id' => $informe->id,
                'usuario_id' => $users->random()->id,
            ]);

            Bitacora::factory()->count(rand(1, 6))->create([
                'informe_id' => $informe->id,
            ]);

            Ubicacion::factory()->count(rand(1, 3))->create([
                'informe_id' => $informe->id,
            ]);
        });
    }
}
