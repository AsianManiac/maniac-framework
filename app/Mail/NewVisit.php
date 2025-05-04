<?php

namespace App\Mail;

use Core\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->from('no-reply@example.com', 'Maniac Framework')
            ->to($this->user->email, $this->user->name)
            ->subject('Welcome to Maniac Framework!')
            ->markdown('emails.welcome')
            ->with(['user' => $this->user])
            ->greeting("Hello {$this->user->name}!")
            ->line('Welcome to the Maniac Framework!')
            ->action('Explore Dashboard', url('/dashboard'))
            ->line('We are excited to have you on board.')
            ->panel('Your account details: <br>Email: ' . $this->user->email)
            ->table([
                ['key' => 'Name', 'value' => $this->user->name],
                ['key' => 'Email', 'value' => $this->user->email],
            ], ['key', 'value'])
            ->signature('The Maniac Team')
            ->footer('© ' . now()->toDateTimeString() . ' Maniac Framework. All rights reserved.');
    }
}
