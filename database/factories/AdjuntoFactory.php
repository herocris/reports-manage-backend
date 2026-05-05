<?php

namespace Database\Factories;

use App\Models\Adjunto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Adjunto>
 */
class AdjuntoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => fake()->url(),
            'descripcion' => fake()->sentence(),
        ];
    }
}
