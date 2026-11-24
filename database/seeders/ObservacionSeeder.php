<?php

namespace Database\Seeders;

use App\Models\Observacion;
use Illuminate\Database\Seeder;

/**
 * Seeds observacion records attached to informes.
 */
class ObservacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Observacion::factory()->count(10)->create();
    }
}
