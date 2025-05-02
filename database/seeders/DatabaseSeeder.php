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
        // Call other seeders here
        $this->call(UsersTableSeeder::class);
        // $this->call(PostsTableSeeder::class);
        // $this->call(PermissionsTableSeeder::class);
        // $this->call(UserPermissionsTableSeeder::class);
    }
}
