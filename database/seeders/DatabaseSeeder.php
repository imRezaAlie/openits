<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DomainSeeder::class,
            TechnologySeeder::class,
            VendorSeeder::class,
            ProjectSeeder::class,
            ApiDocumentationSeeder::class,
            ModernDataStackSeeder::class,
            ServerSeeder::class,
            BpmnSeeder::class,
            SystemDocumentSeeder::class,
            C4Seeder::class,
            AdrSeeder::class,
        ]);
    }
}
