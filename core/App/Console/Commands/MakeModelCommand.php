<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;

/**
 * Command to create a new model class.
 */
class MakeModelCommand extends Command
{
    protected $name = 'make:model';
    protected $description = 'Create a new model class';

    public function handle(array $args): int
    {
        if (empty($args)) {
            $this->error("Please provide a model name.");
            return 1;
        }

        $name = $args[0];
        $withMigration = in_array('--migration', $args) || in_array('-m', $args);
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . 's';

        // Create model
        $stub = <<<EOT
<?php

namespace App\Models;

use Core\Mvc\Model;

class {$name} extends Model
{
    protected array \$fillable = [];
    protected string \$table = '{$table}';
}
EOT;

        $path = BASE_PATH . '/app/Models/' . $name . '.php';
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (file_put_contents($path, $stub) === false) {
            $this->error("Could not write to {$path}");
            return 1;
        }

        $this->info("Created model: {$path}");

        // Create migration if requested
        if ($withMigration) {
            $migrationName = "create_{$table}_table";
            $creator = new \Core\Database\Migrations\MigrationCreator(BASE_PATH . '/database/migrations');
            $file = $creator->create($migrationName, $table);

            $this->info("Created migration: {$file}");
        }

        return 0;
    }
}
