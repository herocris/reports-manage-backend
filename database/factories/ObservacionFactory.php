<?php

namespace Database\Factories;

use App\Models\Informe;
use App\Models\Observacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Observacion>
 */
class ObservacionFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Observacion $observacion): void {
            if ($observacion->numero === null) {
                $observacion->numero = Observacion::query()
                    ->withTrashed()
                    ->where('informe_id', $observacion->informe_id)
                    ->max('numero') + 1;
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero' => null,
            'titulo' => fake()->sentence(5),
            'descripcion' => fake()->paragraph(),
            'usuario_id' => User::factory(),
            'informe_id' => Informe::factory(),
        ];
    }
}
