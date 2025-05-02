<?php

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;

/**
 * Seeder for the users table.
 */
class UsersTableSeeder extends Seeder
{
    /**
     * Run the users table seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $users = [
            [
                'name' => $this->fake('name'),
                'email' => $this->fake('email'),
                'password' => $this->fake('password'),
                'is_active' => $this->fake('boolean'),
                'email_verified_at' => $this->fake('datetime'),
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ],
        ];

        $this->insertMany('users', $users);
    }
}
