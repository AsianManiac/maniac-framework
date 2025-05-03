<?php

namespace Core\App\Console\Commands;

use Core\Database\DB;
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
        // Check for --force in production
        if (config('app.env') === 'production' && !isset($args['--force'])) {
            $this->error('Seeding is disabled in production. Use --force to override.');
            return 1;
        }

        // Initialize database connection
        try {
            DB::init(config('database'));
        } catch (\Exception $e) {
            $this->error("Failed to initialize database: {$e->getMessage()}");
            return 1;
        }

        // Parse --class option
        $class = 'Database\Seeders\DatabaseSeeder';
        if (isset($args['--class'])) {
            $class = $args['--class'];
            // Handle cases where the class is a simple name (e.g., UsersTableSeeder)
            if (!class_exists($class) && !str_starts_with($class, 'Database\\Seeders\\')) {
                $class = 'Database\\Seeders\\' . trim($class, '\\');
            }
        }

        // Check if the seeder class exists
        if (!class_exists($class)) {
            $this->error("Seeder class {$class} not found. Ensure it exists in database/seeders/ and is autoloaded.");
            return 1;
        }

        $this->info("Running seeder: {$class}...");
        try {
            Seed::run($class);
            $this->info("Seeding completed successfully.");
            return 0;
        } catch (\Exception $e) {
            Log::error("Seeding failed: {$e->getMessage()}", ['exception' => $e]);
            $this->error("Seeding failed: {$e->getMessage()}\nCheck logs for details.");
            return 1;
        }
    }
}
