<?php

namespace Core\Auth;

use Core\Foundation\Facade;
use App\Models\User;


/**
 * @method static void setUser(?User $user)
 * @method static User|null user()
 * @method static bool check()
 * @method static bool guest()
 * @method static int|null id()
 *
 * @see \Core\Auth\AuthManager
 */
class Auth extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return AuthManager::class; // Bind AuthManager in App container
    }
}
