<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;

/**
 * Command to generate a new seeder class.
 */
class MakeSeederCommand extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'make:seeder';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder class';

    /**
     * Execute the command.
     *
     * @param array $args The command-line arguments
     * @return int 0 for success, 1 for failure
     */
    public function handle(array $args): int
    {
        // Expect the seeder name as the first positional argument
        $name = $args[0] ?? null;
        if (!$name) {
            $this->error("Seeder name is required.");
            return 1;
        }

        $className = ucfirst($name);
        $fileName = $className . '.php';
        $path = BASE_PATH . '/database/seeders/' . $fileName;

        if (file_exists($path)) {
            $this->error("Seeder {$className} already exists.");
            return 1;
        }

        // Ensure the directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $stub = <<<STUB
<?php

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;

/**
 * Seeder for the {$name} table.
 */
class {$className} extends Seeder
{
    /**
     * Run the {$name} table seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Add your seeding logic here
    }
}
STUB;

        if (file_put_contents($path, $stub) === false) {
            $this->error("Could not write to {$path}");
            return 1;
        }

        $this->info("Seeder {$className} created successfully at {$path}.");
        return 0;
    }
}
