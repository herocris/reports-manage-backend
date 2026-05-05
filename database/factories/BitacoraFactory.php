<?php

namespace Database\Factories;

use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bitacora>
 */
class BitacoraFactory extends Factory
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
            'fecha' => fake()->date(),
            'informe_id' => \App\Models\Informe::factory(),
        ];
    }
}
