<?php

namespace App\Notifications;

use Core\Mail\Mailable;
use Core\Notifications\Notification;

class UserRegistered extends Notification
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): Mailable
    {
        return (new Mailable)
            ->to($notifiable->email, $this->user->name)
            ->subject('Welcome to Maniac Framework!')
            ->greeting("Hello {$this->user->name}!")
            ->line('Thank you for registering with us!')
            ->action('Login Now', url('/login'))
            ->line('We look forward to seeing you.')
            ->signature('The Maniac Team');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => "User {$this->user->name} registered.",
            'user_id' => $this->user->id,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}
