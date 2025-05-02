<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;
use Core\Database\Migrations\MigrationCreator;

/**
 * Command to create a new migration file.
 */
class MakeMigrationCommand extends Command
{
    protected $name = 'make:migration';
    protected $description = 'Create a new migration file';

    public function handle(array $args): int
    {
        if (empty($args)) {
            $this->error("Please provide a migration name.");
            return 1;
        }

        $name = $args[0];
        $create = in_array('--create', $args) ? ($args[array_search('--create', $args) + 1] ?? $name) : null;
        $table = in_array('--table', $args) ? ($args[array_search('--table', $args) + 1] ?? $name) : null;

        try {
            $creator = new MigrationCreator(BASE_PATH . '/database/migrations');
            $file = $creator->create($name, $create, $table);

            $this->info("Created migration: {$file}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to create migration: {$e->getMessage()}");
            return 1;
        }
    }
}
