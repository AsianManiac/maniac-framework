<?php

namespace Core\Notifications;

use Core\Notifications\NotificationSender;

trait Notifiable
{
    /**
     * Send the given notification.
     *
     * @param Notification $notification
     * @return void
     */
    public function notify(Notification $notification): void
    {
        // Resolve the sender from the container and send
        /** @var NotificationSender $sender */
        $sender = app(NotificationSender::class);
        $sender->send($this, $notification); // $this is the notifiable entity (e.g., User)
    }

    /**
     * Send the given notification immediately.
     *
     * @param Notification $notification
     * @return void
     */
    public function notifyNow(Notification $notification): void
    {
        /** @var NotificationSender $sender */
        $sender = app(NotificationSender::class);
        $sender->sendNow($this, $notification);
    }

    /**
     * Get the entity's notifications from the database.
     * (Requires DatabaseChannel and notifications table setup).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany // Or your ORM equivalent
     */
    // public function notifications() {
    //     return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    // }

    /**
     * Get the entity's read notifications from the database.
     */
    // public function readNotifications() { ... }

    /**
     * Get the entity's unread notifications from the database.
     */
    // public function unreadNotifications() { ... }


    /**
     * Get the notification routing information for the given channel.
     * Example: For 'mail', return email address. For 'slack', return webhook URL.
     * Override this in your Notifiable models (e.g., User) if needed.
     *
     * @param string $channel Channel name ('mail', 'slack', 'database', etc.).
     * @param Notification|null $notification The notification instance (optional).
     * @return mixed Routing information (string email, string webhook, etc.).
     */
    public function routeNotificationFor(string $channel, ?Notification $notification = null): mixed
    {
        if ($channel === 'mail') {
            // Assuming the model has an 'email' property
            return $this->email ?? null;
        }
        if ($channel === 'database') {
            // Database channel doesn't usually need explicit routing info here
            return null;
        }
        // Add other channels like 'slack', 'vonage' (sms) etc.
        return null;
    }
}
