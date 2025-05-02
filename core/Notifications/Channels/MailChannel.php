<?php

namespace Core\Notifications\Channels;

use Core\Mail\Mailer;
use Core\Mail\Mailable;
use Symfony\Component\Mime\Address;
use Core\Notifications\Notification;

class MailChannel implements ChannelInterface
{

    protected Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send the given notification via mail.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        // Get the Mailable instance from the notification
        $message = $notification->toMail($notifiable);

        if (!$message instanceof Mailable) {
            // Notification doesn't support mail channel or returned null
            return;
        }

        // Get recipient routing info from the Notifiable entity
        $recipient = $this->getRecipient($notifiable, $notification);
        if (!$recipient) {
            // Log warning: No recipient found for mail channel
            return;
        }

        // Send the mailable via the Mailer service
        $this->mailer->send($message->to($recipient)); // Ensure 'to' is set
    }

    /**
     * Get the recipient address for the notification.
     */
    protected function getRecipient(mixed $notifiable, Notification $notification): string|Address|null
    {
        if (method_exists($notifiable, 'routeNotificationFor')) {
            $route = $notifiable->routeNotificationFor('mail', $notification);
            if ($route) return $route;
        }
        // Fallback if routeNotificationFor doesn't exist or returns null
        if (isset($notifiable->email)) {
            return new Address($notifiable->email, $notifiable->name ?? null);
        }
        return null;
    }
}
