<?php

use Core\Mail\Mail;
use Core\Notifications\Notifiable;
use Core\Notifications\NotificationSender;

if (!function_exists('notify')) {
    /**
     * Send a notification to one or more notifiables.
     *
     * @param mixed $notifiables
     * @param \Core\Notifications\Notification $notification
     * @param bool $now Send immediately (bypasses queue)
     * @return void
     */
    function notify($notifiables, $notification, bool $now = false): void
    {
        $sender = app(NotificationSender::class);
        if ($now) {
            $sender->sendNow($notifiables, $notification);
        } else {
            $sender->send($notifiables, $notification);
        }
    }
}

if (!function_exists('notifiable')) {
    /**
     * Wrap an entity to make it notifiable.
     *
     * @param mixed $entity
     * @return object
     */
    function notifiable($entity)
    {
        return new class($entity) {
            use Notifiable;

            protected $entity;

            public function __construct($entity)
            {
                $this->entity = $entity;
            }

            public function __get($name)
            {
                return $this->entity->$name ?? null;
            }

            public function routeNotificationFor(string $channel, ?\Core\Notifications\Notification $notification = null): mixed
            {
                if (method_exists($this->entity, 'routeNotificationFor')) {
                    return $this->entity->routeNotificationFor($channel, $notification);
                }
                if ($channel === 'mail' && isset($this->entity->email)) {
                    return $this->entity->email;
                }
                return null;
            }
        };
    }
}

if (!function_exists('mailit')) {
    /**
     * Access the Mail facade.
     *
     * @return \Core\Mail\Mail
     */
    function mailit()
    {
        return new Mail();
    }
}
