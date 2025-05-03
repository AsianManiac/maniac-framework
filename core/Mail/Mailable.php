<?php

namespace Core\Mail;

use Exception;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

abstract class Mailable
{
    public ?string $subject = null;
    public array $to = [];
    public array $cc = [];
    public array $bcc = [];
    public array $replyTo = [];
    public ?Address $from = null;
    public ?string $view = null;
    public array $viewData = [];
    public ?string $markdown = null;
    public ?string $textView = null;
    public array $attachments = [];
    public array $rawAttachments = [];
    protected array $contentComponents = [];
    protected bool $usingComponents = false;

    /**
     * Fluent configuration methods.
     */
    public function from(string $address, ?string $name = null): static
    {
        $this->from = new Address($address, $name ?? '');
        return $this;
    }

    public function to(string|array $address, ?string $name = null): static
    {
        $this->addRecipients($this->to, $address, $name);
        return $this;
    }

    public function cc(string|array $address, ?string $name = null): static
    {
        $this->addRecipients($this->cc, $address, $name);
        return $this;
    }

    public function bcc(string|array $address, ?string $name = null): static
    {
        $this->addRecipients($this->bcc, $address, $name);
        return $this;
    }

    public function replyTo(string|array $address, ?string $name = null): static
    {
        $this->addRecipients($this->replyTo, $address, $name);
        return $this;
    }

    protected function addRecipients(array &$list, string|array $address, ?string $name = null): void
    {
        if (is_array($address)) {
            foreach ($address as $key => $value) {
                if (is_string($key)) {
                    $list[] = new Address($key, $value);
                } else {
                    $list[] = new Address($value);
                }
            }
        } else {
            $list[] = new Address($address, $name ?? '');
        }
    }

    public function subject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function view(string $viewName, array $data = []): static
    {
        if ($this->usingComponents) {
            throw new Exception('Cannot use view() when content components (e.g., greeting, line) are used.');
        }
        $this->view = $viewName;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }

    public function markdown(string $viewName, array $data = []): static
    {
        if ($this->usingComponents) {
            throw new Exception('Cannot use markdown() when content components (e.g., greeting, line) are used.');
        }
        $this->markdown = $viewName;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }

    public function text(string $viewName, array $data = []): static
    {
        $this->textView = $viewName;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }

    public function with(array|string $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }
        return $this;
    }

    public function attach(string $file, array $options = []): static
    {
        $this->attachments[] = ['path' => $file, 'options' => $options];
        return $this;
    }

    public function attachData(string $data, string $name, array $options = []): static
    {
        $options['name'] = $options['name'] ?? $name;
        $this->rawAttachments[] = ['data' => $data, 'options' => $options];
        return $this;
    }

    /**
     * Content component methods.
     */
    public function greeting(string $greeting): static
    {
        $this->contentComponents[] = ['type' => 'greeting', 'value' => $greeting];
        return $this;
    }

    public function line(string $text): static
    {
        $this->contentComponents[] = ['type' => 'line', 'value' => $text];
        return $this;
    }

    public function action(string $text, string $url): static
    {
        $this->contentComponents[] = ['type' => 'action', 'value' => ['text' => $text, 'url' => $url]];
        return $this;
    }

    public function panel(string $content): static
    {
        $this->contentComponents[] = ['type' => 'panel', 'value' => $content];
        return $this;
    }

    public function table(array $data, array $columns = []): static
    {
        $this->contentComponents[] = ['type' => 'table', 'value' => ['data' => $data, 'columns' => $columns]];
        return $this;
    }

    public function signature(string $signature): static
    {
        $this->contentComponents[] = ['type' => 'signature', 'value' => $signature];
        return $this;
    }

    public function footer(string $footer): static
    {
        $this->contentComponents[] = ['type' => 'footer', 'value' => $footer];
        return $this;
    }

    /**
     * Convert mailable data to array for template rendering.
     */
    public function toArray(): array
    {
        return [
            'subject' => $this->subject,
            'to' => array_map(fn($addr) => $addr->toString(), $this->to),
            'cc' => array_map(fn($addr) => $addr->toString(), $this->cc),
            'bcc' => array_map(fn($addr) => $addr->toString(), $this->bcc),
            'replyTo' => array_map(fn($addr) => $addr->toString(), $this->replyTo),
            'from' => $this->from ? $this->from->toString() : null,
            'components' => $this->contentComponents,
            'data' => $this->viewData,
        ];
    }

    /**
     * Build the message content.
     */
    abstract public function build();

    /**
     * Build the Symfony Email instance.
     */
    public function buildSymfonyMessage(Mailer $mailer): Email
    {
        $this->build();

        $email = new Email();

        if ($this->from ?? $mailer->getGlobalFromAddress()) {
            $email->from($this->from ?? $mailer->getGlobalFromAddress());
        }

        if ($this->to) $email->to(...$this->to);
        if ($this->cc) $email->cc(...$this->cc);
        if ($this->bcc) $email->bcc(...$this->bcc);
        if ($this->replyTo) $email->replyTo(...$this->replyTo);

        if ($this->subject) $email->subject($this->subject);

        [$htmlContent, $textContent] = $mailer->renderMailableContent($this);

        if ($htmlContent) $email->html($htmlContent);
        if ($textContent) $email->text($textContent);

        foreach ($this->attachments as $attachment) {
            $email->attachFromPath(
                $attachment['path'],
                $attachment['options']['name'] ?? null,
                $attachment['options']['mime'] ?? null
            );
        }
        foreach ($this->rawAttachments as $attachment) {
            $email->attach(
                $attachment['data'],
                $attachment['options']['name'] ?? 'attachment.dat',
                $attachment['options']['mime'] ?? 'application/octet-stream'
            );
        }

        return $email;
    }

    /**
     * Get content components for rendering.
     */
    public function getContentComponents(): array
    {
        return $this->contentComponents;
    }
}
