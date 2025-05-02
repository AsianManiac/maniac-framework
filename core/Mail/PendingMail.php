<?php

namespace Core\Mail;

use Symfony\Component\Mime\Address;

class PendingMail
{
    protected Mailer $mailer;
    protected array $to = [];
    protected array $cc = [];
    protected array $bcc = [];
    protected array $replyTo = [];

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function to(mixed $users): self
    {
        $this->addRecipients($this->to, $users);
        return $this;
    }

    public function cc(mixed $users): self
    {
        $this->addRecipients($this->cc, $users);
        return $this;
    }

    public function bcc(mixed $users): self
    {
        $this->addRecipients($this->bcc, $users);
        return $this;
    }

    public function replyTo(mixed $users): self
    {
        $this->addRecipients($this->replyTo, $users);
        return $this;
    }

    public function send(Mailable $mailable): void
    {
        if ($this->to) $mailable->to = array_merge($mailable->to, $this->to);
        if ($this->cc) $mailable->cc = array_merge($mailable->cc, $this->cc);
        if ($this->bcc) $mailable->bcc = array_merge($mailable->bcc, $this->bcc);
        if ($this->replyTo) $mailable->replyTo = array_merge($mailable->replyTo, $this->replyTo);

        $this->mailer->send($mailable);
    }

    public function sendNow(Mailable $mailable): void
    {
        if ($this->to) $mailable->to = array_merge($mailable->to, $this->to);
        if ($this->cc) $mailable->cc = array_merge($mailable->cc, $this->cc);
        if ($this->bcc) $mailable->bcc = array_merge($mailable->bcc, $this->bcc);
        if ($this->replyTo) $mailable->replyTo = array_merge($mailable->replyTo, $this->replyTo);

        $this->mailer->sendNow($mailable);
    }

    public function queue(Mailable $mailable): void
    {
        if ($this->to) $mailable->to = array_merge($mailable->to, $this->to);
        if ($this->cc) $mailable->cc = array_merge($mailable->cc, $this->cc);
        if ($this->bcc) $mailable->bcc = array_merge($mailable->bcc, $this->bcc);
        if ($this->replyTo) $mailable->replyTo = array_merge($mailable->replyTo, $this->replyTo);

        $this->mailer->queue($mailable);
    }

    protected function addRecipients(array &$list, mixed $users): void
    {
        if ($users instanceof Address) {
            $list[] = $users;
            return;
        }
        if (is_string($users)) {
            $list[] = new Address($users);
            return;
        }
        if (is_array($users)) {
            foreach ($users as $key => $value) {
                if (is_string($key)) {
                    $list[] = new Address($key, $value);
                } elseif (is_string($value)) {
                    $list[] = new Address($value);
                } elseif ($value instanceof Address) {
                    $list[] = $value;
                }
            }
            return;
        }
    }
}
