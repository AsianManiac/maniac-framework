<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;
use Core\Console\Application;

/**
 * Command to list all available console commands.
 */
class ListCommand extends Command
{
    protected $name = 'list';
    protected $description = 'List all available commands';

    public function handle(array $args): int
    {
        $this->info("Maniac Framework Console\n");
        $this->info("Usage:");
        $this->info("  command [options] [arguments]\n");
        $this->info("Available commands:");

        $commands = $this->app->resolve('console')->getCommands();
        foreach ($commands as $command) {
            $this->info("  {$command->getName()}  {$command->getDescription()}");
        }

        return 0;
    }
}
