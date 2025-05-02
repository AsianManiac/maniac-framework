<?php

namespace Core\Mail;

use Core\Foundation\Facade;

/**
 * @method static Mailer mailer(?string $name = null) Get a mailer instance.
 * @method static void send(Mailable $mailable) Send a mailable (handles queueing).
 * @method static void sendNow(Mailable $mailable) Send a mailable immediately.
 * @method static void queue(Mailable $mailable) Queue a mailable.
 * @method static Mailer to(string|array $address, ?string $name = null) Set recipient (starts sending process).
 * @method static PendingMail cc(string|array $address, ?string $name = null)
 * @method static PendingMail bcc(string|array $address, ?string $name = null)
 * @method static PendingMail replyTo(string|array $address, ?string $name = null)
 *
 * @see \Core\Mail\Mailer
 * @see \Core\Mail\PendingMail // For Mail::to()->send() fluent interface
 */
class Mail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Mailer::class; // Bind Mailer in App container
    }

    // --- Implement Mail::to() fluent interface ---
    // This requires a PendingMail class to hold state before send()

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \Core\Mail\PendingMail
     */
    public static function to($users)
    {
        return (new PendingMail(static::resolveFacadeInstance(static::getFacadeAccessor())))->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \Core\Mail\PendingMail
     */
    public static function cc($users)
    {
        return (new PendingMail(static::resolveFacadeInstance(static::getFacadeAccessor())))->cc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \Core\Mail\PendingMail
     */
    public static function bcc($users): PendingMail
    {
        return (new PendingMail(static::resolveFacadeInstance(static::getFacadeAccessor())))->bcc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \Core\Mail\PendingMail
     */
    public static function replyTo($users): PendingMail
    {
        return (new PendingMail(static::resolveFacadeInstance(static::getFacadeAccessor())))->replyTo($users);
    }
}
