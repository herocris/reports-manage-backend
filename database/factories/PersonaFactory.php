<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Persona> */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'numero_identificacion' => sprintf('%04d-%04d-%05d', fake()->numberBetween(101, 1899), fake()->numberBetween(1950, 2024), fake()->numberBetween(1, 99999)),
        ];
    }
}
