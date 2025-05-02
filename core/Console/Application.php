<?php

namespace Core\Console;

use Closure;
use Exception;

/**
 * The console application that handles command execution.
 *
 * This class manages a registry of commands and dispatches incoming CLI arguments
 * to the appropriate command handler.
 */
class Application
{
    /**
     * The registered commands.
     *
     * @var array<string, Command>
     */
    protected $commands = [];

    /**
     * Add a command to the application.
     *
     * @param Command $command
     * @return void
     */
    public function add(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    /**
     * Get all registered commands.
     *
     * @return array<string, Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Run the console application with the given arguments.
     *
     * @param array $argv The command-line arguments.
     * @return int The command exit code.
     */
    public function run(array $argv): int
    {
        $scriptName = array_shift($argv); // Remove script name
        $commandName = array_shift($argv) ?? 'list'; // Default to list

        if ($commandName === '--help' || $commandName === '-h') {
            $commandName = 'list';
        }

        if (!isset($this->commands[$commandName])) {
            echo "Error: Command '{$commandName}' is not defined.\n\n";
            echo "Available commands:\n";
            foreach ($this->commands as $cmd) {
                echo "  {$cmd->getName()}\n";
            }
            return 1;
        }

        $command = $this->commands[$commandName];
        return $command->handle($argv);
    }

    /**
     * Parse command-line arguments and options.
     *
     * @param array $args Raw arguments
     * @return array Parsed arguments and options
     */
    protected function parseArguments(array $args): array
    {
        $parsed = [];
        $positional = [];

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                // Handle options like --class=Value
                $parts = explode('=', ltrim($arg, '--'), 2);
                $key = $parts[0];
                $value = $parts[1] ?? true;
                $parsed["--{$key}"] = $value;
            } else {
                // Handle positional arguments
                $positional[] = $arg;
            }
        }

        // Assign positional arguments
        foreach ($positional as $index => $value) {
            $parsed[$index] = $value;
        }

        return $parsed;
    }

    /**
     * List available commands.
     *
     * @return void
     */
    protected function listCommands(): void
    {
        echo "Available commands:\n";
        foreach ($this->commands as $name => $command) {
            echo "  {$name} - {$command->getDescription()}\n";
        }
    }
}
