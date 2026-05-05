<?php

namespace Database\Seeders;

use App\Models\TipoBien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeds tipo de bien (asset category) reference records.
 */
class TipoBienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoBien::factory()->count(10)->create();
    }
}
