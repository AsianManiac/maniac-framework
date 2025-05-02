<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;

/**
 * Command to serve the application using PHP's built-in development server.
 */
class ServeCommand extends Command
{
    protected $name = 'serve';
    protected $description = 'Serve the application on the PHP development server';

    public function handle(array $args): int
    {
        $defaultHost = env('SERVER_HOST', 'localhost');
        $defaultPort = env('SERVER_PORT', 8001);
        $host = $defaultHost;
        $port = $defaultPort;
        $publicDir = BASE_PATH . '/public';

        // Parse arguments
        foreach ($args as $key => $arg) {
            if (strpos($arg, '--port=') === 0) {
                $port = substr($arg, strlen('--port='));
            } elseif ($arg === '--port' && isset($args[$key + 1])) {
                $port = $args[$key + 1];
            } elseif (strpos($arg, '--host=') === 0) {
                $host = substr($arg, strlen('--host='));
            } elseif ($arg === '--host' && isset($args[$key + 1])) {
                $host = $args[$key + 1];
            }
        }

        if (!is_numeric($port) || $port < 1024 || $port > 65535) {
            $this->error("Invalid port number '{$port}'. Please use a port between 1024 and 65535.");
            return 1;
        }

        if (!is_dir($publicDir)) {
            $this->error("Public directory not found at {$publicDir}");
            return 1;
        }

        $this->info("Starting Maniac development server: http://{$host}:{$port}");
        $this->info("Document root is: {$publicDir}");
        $this->info("Press Ctrl+C to stop the server.");

        passthru('"' . PHP_BINARY . '"' . " -S {$host}:{$port} -t " . escapeshellarg($publicDir) . ' ' . escapeshellarg($publicDir . '/index.php'), $return_var);
        return $return_var;
    }
}
