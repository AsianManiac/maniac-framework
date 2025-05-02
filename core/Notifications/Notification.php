<?php

namespace Core\Notifications;

use Core\Foundation\Facade;
use Core\Notifications\NotificationSender;

/**
 * @method static void send(mixed $notifiables, BaseNotification $notification)
 * @method static void sendNow(mixed $notifiables, BaseNotification $notification)
 * @method static void route(string $channel, mixed $route) // For on-demand notifications
 *
 * @see \Core\Notifications\NotificationSender
 */
class Notification extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NotificationSender::class; // Bind NotificationSender in App
    }

    // Implement Notification::route() for on-demand notifications later if needed
}
