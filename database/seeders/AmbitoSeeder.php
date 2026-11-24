<?php

namespace Database\Seeders;

use App\Models\Ambito;
use Illuminate\Database\Seeder;

/**
 * Seeds ambito (scope) records used in informe testing and demos.
 */
class AmbitoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ambito::factory()->count(10)->create();
    }
}
