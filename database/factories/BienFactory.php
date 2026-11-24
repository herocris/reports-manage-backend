<?php

namespace Database\Factories;

use App\Models\Bien;
use App\Models\TipoBien;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bien>
 */
class BienFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'descripcion' => fake()->sentence(),
            'tipobien_id' => TipoBien::factory(),
        ];
    }
}
