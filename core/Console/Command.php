<?php

namespace Core\Console;

use Core\Foundation\App;

/**
 * Base class for all console commands.
 *
 * Provides common functionality and enforces a standard interface for commands.
 */
abstract class Command
{
    /**
     * The application instance.
     *
     * @var App
     */
    protected $app;

    /**
     * The name of the command.
     *
     * @var string
     */
    protected $name;

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description;

    /**
     * Create a new command instance.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Get the command name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Execute the command.
     *
     * @param array $args The command-line arguments.
     * @return int The command exit code (0 for success, non-zero for failure).
     */
    abstract public function handle(array $args): int;

    /**
     * Output a message to the console.
     *
     * @param string $message
     * @return void
     */
    protected function info(string $message): void
    {
        echo $message . "\n";
    }

    /**
     * Output an error message to the console.
     *
     * @param string $message
     * @return void
     */
    protected function error(string $message): void
    {
        echo "Error: $message\n";
    }
}
