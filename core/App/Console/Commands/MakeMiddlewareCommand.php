<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;

/**
 * Command to create a new middleware class.
 */
class MakeMiddlewareCommand extends Command
{
    protected $name = 'make:middleware';
    protected $description = 'Create a new middleware class';

    public function handle(array $args): int
    {
        if (empty($args)) {
            $this->error("Please provide a middleware name.");
            return 1;
        }

        $name = $args[0];
        if (!str_ends_with($name, 'Middleware')) {
            $name .= 'Middleware';
        }

        $stub = <<<EOT
<?php

namespace App\Http\Middleware;

use Closure;
use Core\Http\Request;

class {$name}
{
    public function handle(Request \$request, Closure \$next)
    {
        // Add middleware logic here
        return \$next(\$request);
    }
}
EOT;

        $path = BASE_PATH . '/app/Http/Middleware/' . $name . '.php';
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (file_put_contents($path, $stub) === false) {
            $this->error("Could not write to {$path}");
            return 1;
        }

        $this->info("Created middleware: {$path}");
        return 0;
    }
}
