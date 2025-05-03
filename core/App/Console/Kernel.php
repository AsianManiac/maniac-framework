<?php

namespace Core\App\Console;

use Core\Foundation\App;
use Core\Console\Application;
use Core\App\Console\Commands\ListCommand;
use Core\App\Console\Commands\SeedCommand;
use Core\App\Console\Commands\ServeCommand;
use Core\App\Console\Commands\MigrateCommand;
use Core\App\Console\Commands\MakeModelCommand;
use Core\App\Console\Commands\MakeSeederCommand;
use Core\App\Console\Commands\KeyGenerateCommand;
use Core\App\Console\Commands\MakeMailableCommand;
use Core\App\Console\Commands\MakeMigrationCommand;
use Core\App\Console\Commands\MakeControllerCommand;
use Core\App\Console\Commands\MakeMiddlewareCommand;
use Core\App\Console\Commands\MigrateRollbackCommand;
use Core\App\Console\Commands\MakeNotificationCommand;

/**
 * The Console Kernel for the Maniac Framework.
 *
 * This class is responsible for registering commands and handling command execution
 * via the console application.
 */
class Kernel
{
    /**
     * The application instance.
     *
     * @var App
     */
    protected $app;

    /**
     * The console application that handles command execution.
     *
     * @var Application
     */
    protected $artisan;

    /**
     * The commands provided by the application.
     *
     * @var array
     */
    protected $commands = [
        ServeCommand::class,
        ListCommand::class,
        KeyGenerateCommand::class,
        MakeMigrationCommand::class,
        MakeModelCommand::class,
        MakeMailableCommand::class,
        MakeNotificationCommand::class,
        MakeControllerCommand::class,
        MakeMiddlewareCommand::class,
        MigrateCommand::class,
        SeedCommand::class,
        MakeSeederCommand::class,
        MigrateRollbackCommand::class,
    ];

    /**
     * Create a new console kernel instance.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->artisan = new Application();
        $this->registerCommands();
    }

    /**
     * Register the commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        foreach ($this->commands as $command) {
            $this->artisan->add(new $command($this->app));
        }
    }

    /**
     * Handle a console command.
     *
     * @param array $argv
     * @return int
     */
    public function handle(array $argv): int
    {
        try {
            return $this->artisan->run($argv);
        } catch (\Throwable $e) {
            echo "Error: {$e->getMessage()}\n";
            return 1;
        }
    }
}
