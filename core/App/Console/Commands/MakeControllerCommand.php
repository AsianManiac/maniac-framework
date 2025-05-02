<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;

/**
 * Command to create a new controller class.
 */
class MakeControllerCommand extends Command
{
    protected $name = 'make:controller';
    protected $description = 'Create a new controller class';

    public function handle(array $args): int
    {
        if (empty($args)) {
            $this->error("Please provide a controller name.");
            return 1;
        }

        $name = $args[0];
        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        $stub = <<<EOT
<?php

namespace App\Http\Controllers;

use Core\Mvc\Controller;

class {$name} extends Controller
{
    public function index()
    {
        return \$this->view('{$name}.index');
    }
}
EOT;

        $path = BASE_PATH . '/app/Http/Controllers/' . $name . '.php';
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (file_put_contents($path, $stub) === false) {
            $this->error("Could not write to {$path}");
            return 1;
        }

        $this->info("Created controller: {$path}");
        return 0;
    }
}
