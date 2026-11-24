<?php

namespace Database\Factories;

use App\Models\Informe;
use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ubicacion> */
class UbicacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'latitud' => fake()->randomFloat(6, -89, 89),
            'longitud' => fake()->randomFloat(6, -179, 179),
            'descripcion' => fake()->sentence(),
            'informe_id' => Informe::factory(),
        ];
    }
}
