<?php

namespace Core\Facades;

use Core\Foundation\Facade;

/**
 * Facade for the console application.
 *
 * @method static void add(\Core\Console\Command $command)
 * @method static int run(array $argv)
 */
class Maniac extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'console';
    }
}
