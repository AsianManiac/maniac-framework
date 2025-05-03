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
                'email' => 'user13@example.com',
                'password' => $this->fake('password'),
                'is_active' => $this->fake('boolean'),
                'email_verified_at' => $this->fake('datetime'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // [
            //     'name' => $this->fake('name'),
            //     'email' => $this->fake('email'),
            //     'password' => $this->fake('password'),
            //     'is_active' => $this->fake('boolean'),
            //     'email_verified_at' => $this->fake('datetime'),
            //     'created_at' => date('Y-m-d H:i:s'),
            //     'updated_at' => date('Y-m-d H:i:s'),
            // ],
        ];

        $this->insertMany('users', $users);
    }
}
