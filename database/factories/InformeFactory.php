<?php

namespace Database\Factories;

use App\Models\Informe;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Informe>
 */
class InformeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $letters = strtoupper(fake()->lexify('???'));
        $digits = fake()->numerify('###');

        return [
            'expediente' => 'EXP-'.$letters.$digits,
            'titulo' => fake()->sentence(4),
            'producto_id' => Producto::factory(),
            'fecha_inicio' => fake()->date(),
            'fecha_final' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
            'usuariocreador_id' => User::factory(),
        ];
    }
}
