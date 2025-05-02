<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;
use Core\Database\Migrations\Migrator;

/**
 * Command to rollback the last batch of database migrations.
 */
class MigrateRollbackCommand extends Command
{
    protected $name = 'migrate:rollback';
    protected $description = 'Rollback the last batch of database migrations';

    public function handle(array $args): int
    {
        try {
            $migrator = new Migrator(
                BASE_PATH . '/database/migrations',
                $this->app->resolve('db')
            );

            $migrator->rollback();

            $this->info("Rolled back successfully.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Rollback failed: {$e->getMessage()}");
            return 1;
        }
    }
}
