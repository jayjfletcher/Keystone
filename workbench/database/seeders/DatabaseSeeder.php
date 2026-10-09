<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // `composer serve` signs in as test@example.com (testbench.yaml); the
        // other two author part of the demo catalog's history.
        UserFactory::new()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        UserFactory::new()->create(['name' => 'Maria Editor', 'email' => 'maria@example.com']);
        UserFactory::new()->create(['name' => 'Sam Reviewer', 'email' => 'sam@example.com']);

        // Showroom records versions and syncs search through model events,
        // so the demo catalog is seeded with them on.
        $this->call(ShowroomSeeder::class);
    }
}
