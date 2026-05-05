<?php

namespace Database\Seeders;

use App\Models\Bien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds bien (asset) records associated with tipos de bien.
 */
class BienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bien::factory()->count(10)->create();
    }
}
