<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Institucion> */
class InstitucionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'contacto' => fake()->name(),
            'telefono' => sprintf('%04d-%04d', fake()->numberBetween(1000, 9999), fake()->numberBetween(1000, 9999)),
        ];
    }
}
