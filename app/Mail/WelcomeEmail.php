<?php

namespace App\Mail;

use Core\Mail\Mailable;

/**
 * Welcome email mailable.
 */
class WelcomeEmail extends Mailable
{
    protected $user;

    /**
     * Create a new message instance.
     *
     * @param object $user
     */
    public function __construct(object $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Welcome to Maniac Framework')
            ->markdown('emails.welcome')
            ->with(['name' => $this->user->name]);
    }
}
