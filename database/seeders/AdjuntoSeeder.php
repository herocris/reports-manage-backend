<?php

namespace Database\Seeders;

use App\Models\Adjunto;
use App\Models\Bien;
use App\Models\Informe;
use App\Models\Persona;
use Illuminate\Database\Seeder;

/**
 * Seeds adjunto (file attachment) records linked to Informe, Persona, and Bien models.
 */
class AdjuntoSeeder extends Seeder
{
    public function run(): void
    {
        $owners = collect()
            ->merge(Informe::all()->map(fn (Informe $m) => ['id' => $m->id, 'type' => Informe::class]))
            ->merge(Persona::all()->map(fn (Persona $m) => ['id' => $m->id, 'type' => Persona::class]))
            ->merge(Bien::all()->map(fn (Bien $m) => ['id' => $m->id, 'type' => Bien::class]));

        collect()->times(10)->each(function () use ($owners): void {
            $owner = $owners->random();

            Adjunto::factory()->create([
                'adjuntable_id' => $owner['id'],
                'adjuntable_type' => $owner['type'],
            ]);
        });
    }
}
