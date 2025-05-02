<?php

namespace Core\Notifications\Channels;

use Core\Notifications\Notification;

interface ChannelInterface
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable The entity receiving the notification.
     * @param Notification $notification The notification instance.
     * @return void
     */
    public function send(mixed $notifiable, Notification $notification): void;
}
