<?php

namespace Database\Factories;

use App\Models\Observacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Observacion>
 */
class ObservacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'descripcion' => fake()->paragraph(),
            'usuario_id' => \App\Models\User::factory(),
            'informe_id' => \App\Models\Informe::factory(),
        ];
    }
}
