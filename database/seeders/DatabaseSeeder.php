<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Orchestrates the full database seed by invoking all individual seeders
 * in the required dependency order.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            AmbitoSeeder::class,
            TipoBienSeeder::class,
            ProductoSeeder::class,
            InstitucionSeeder::class,
            BienSeeder::class,
            PersonaSeeder::class,
            InformeSeeder::class,
            AdjuntoSeeder::class,
        ]);
    }
}
