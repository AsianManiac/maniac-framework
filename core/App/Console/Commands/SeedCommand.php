<?php

namespace Core\App\Console\Commands;

use Core\Facades\Seed;
use Core\Console\Command;
use Core\Logging\Log;

/**
 * Command to run database seeders.
 */
class SeedCommand extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'db:seed';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Run the database seeders';

    /**
     * Execute the command.
     *
     * @param array $args The command-line arguments
     * @return int 0 for success, 1 for failure
     */
    public function handle(array $args): int
    {
        // Parse --class option
        $class = 'Database\Seeders\DatabaseSeeder';
        if (isset($args['--class'])) {
            $class = $args['--class'];
            error_log($class);
            // Handle cases where the class is in the app namespace
            if (!class_exists($class) && !str_starts_with($class, 'App\\')) {
                $class = 'Database\\Seeders\\' . $class;
            }
        }

        $this->info("Running seeder: {$class}...");
        try {
            Seed::run($class);
            $this->info("Seeding completed successfully.");
            return 0;
        } catch (\Exception $e) {
            Log::error("Seeding failed: {$e->getMessage()}\nCheck logs for details.");
            $this->error("Seeding failed: {$e->getMessage()}\nCheck logs for details.");
            return 1;
        }
    }
}
