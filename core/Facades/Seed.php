<?php

namespace Core\Facades;

use Core\Foundation\Facade;
use Database\Seeders\DatabaseSeeder;

/**
 * Facade for database seeding operations.
 *
 * @method static void run(string $seeder = DatabaseSeeder::class)
 */
class Seed extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'seeder';
    }

    /**
     * Run the specified seeder or the default DatabaseSeeder.
     *
     * @param string $seeder
     * @return void
     */
    public static function run(string $seeder = DatabaseSeeder::class): void
    {
        $seederInstance = new $seeder();
        $seederInstance->run();
    }
}
