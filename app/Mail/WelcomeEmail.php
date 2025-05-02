<?php

namespace App\Mail;

use App\Models\User;
use Core\Mail\Mailable;
use Core\Contracts\Queue\ShouldQueue;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    public User $user;

    /**
     * Create a new message instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject('Welcome to Maniac Framework, ' . $this->user->name)
            // ->from('noreply@maniac.app', 'Maniac NoReply') // Optional override
            ->markdown('mail.welcome', ['url' => url('/dashboard')]); // Use markdown view
        // Or ->view('mail.welcome-html')
        // Or ->text('mail.welcome-text')
    }
}
