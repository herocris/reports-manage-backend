<?php

namespace Database\Factories;

use App\Models\Observacion;
use App\Models\ObservacionComentario;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ObservacionComentario>
 */
class ObservacionComentarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'observacion_id' => Observacion::factory(),
            'usuario_id' => User::factory(),
            'contenido' => fake()->paragraph(),
        ];
    }
}
