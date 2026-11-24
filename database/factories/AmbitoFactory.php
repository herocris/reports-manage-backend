<?php

namespace Database\Factories;

use App\Models\Ambito;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ambito> */
class AmbitoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
            'descripcion' => fake()->sentence(),
        ];
    }
}
