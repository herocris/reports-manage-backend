<?php

namespace Database\Seeders;

use App\Models\Bitacora;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds bitacora (log entry) records attached to informes.
 */
class BitacoraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bitacora::factory()->count(10)->create();
    }
}
