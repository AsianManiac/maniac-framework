<?php

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;

/**
 * Main database seeder to call other seeders.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            // PostsTableSeeder::class,
            // PermissionsTableSeeder::class,
            // UserPermissionsTableSeeder::class,
        ]);
    }
}
