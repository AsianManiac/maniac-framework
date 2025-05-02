<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;
use Core\Database\Migrations\Migrator;
use Core\Logging\Log;

/**
 * Command to run pending database migrations.
 */
class MigrateCommand extends Command
{
    protected $name = 'migrate';
    protected $description = 'Run pending database migrations';

    public function handle(array $args): int
    {
        try {
            $migrator = new Migrator(
                BASE_PATH . '/database/migrations',
                $this->app->resolve('db')
            );

            $migrator->run();

            $this->info("Migrated successfully.");
            return 0;
        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->error("Migration failed: {$e->getMessage()}");
            return 1;
        }
    }
}
