<?php

namespace Database\Seeders;

use App\Models\Observacion;
use App\Models\ObservacionComentario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ObservacionComentarioSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Observacion::query()
            ->whereDoesntHave('comentarios')
            ->with('informe:id,usuariocreador_id')
            ->chunkById(100, function (Collection $observaciones): void {
                foreach ($observaciones as $observacion) {
                    ObservacionComentario::factory()
                        ->count(fake()->numberBetween(1, 3))
                        ->create([
                            'observacion_id' => $observacion->id,
                            'usuario_id' => $observacion->informe->usuariocreador_id,
                        ]);
                }
            });
    }
}
