<?php

namespace Core\Mail;

use Exception;
use Throwable;
use Core\Logging\Log;
use Core\View\NiacEngine;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Core\Contracts\Queue\ShouldQueue;
use Symfony\Component\Mailer\Transport;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Mailer\MailerInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\Mailer\Transport\TransportInterface;

class Mailer
{
    protected array $config;
    protected NiacEngine $viewEngine;
    protected ?MailerInterface $symfonyMailer = null;
    protected string $symfonyMailerName = '';
    protected ?Address $globalFrom = null;
    protected array $transports = [];

    public function __construct(array $config, NiacEngine $viewEngine)
    {
        $this->config = $config;
        $this->viewEngine = $viewEngine;

        if (isset($config['from']['address'])) {
            $this->globalFrom = new Address($config['from']['address'], $config['from']['name'] ?? '');
        }
    }

    /**
     * Get the Symfony Mailer instance for a given mailer name.
     */
    protected function getSymfonyMailer(?string $mailerName = null): MailerInterface
    {
        $mailerName = $mailerName ?? $this->config['default'] ?? 'smtp';

        if (isset($this->symfonyMailer) && $this->symfonyMailerName === $mailerName) {
            return $this->symfonyMailer;
        }

        $transport = $this->getTransport($mailerName);
        $this->symfonyMailer = new \Symfony\Component\Mailer\Mailer($transport);
        $this->symfonyMailerName = $mailerName;

        return $this->symfonyMailer;
    }

    /**
     * Get or create the TransportInterface for a given mailer name.
     */
    protected function getTransport(?string $mailerName = null): TransportInterface
    {
        $mailerName = $mailerName ?? $this->config['default'] ?? 'smtp';

        if (isset($this->transports[$mailerName])) {
            return $this->transports[$mailerName];
        }

        if (!isset($this->config['mailers'][$mailerName])) {
            throw new \InvalidArgumentException("Mailer [{$mailerName}] not configured.");
        }

        $config = $this->config['mailers'][$mailerName];
        $dsn = $config['dsn'] ?? $this->composeDsn($config);

        if (!$dsn) {
            throw new \InvalidArgumentException("DSN could not be determined for mailer [{$mailerName}].");
        }

        try {
            $this->transports[$mailerName] = Transport::fromDsn($dsn);
            return $this->transports[$mailerName];
        } catch (\Throwable $e) {
            throw new \RuntimeException("Could not create mail transport for [{$mailerName}]: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Compose DSN from individual config parameters.
     */
    protected function composeDsn(array $config): ?string
    {
        $transport = $config['transport'] ?? null;
        if ($transport === 'smtp') {
            $scheme = ($config['encryption'] ?? 'smtp') === 'tls' ? 'smtp' : 'smtps';
            $user = $config['username'] ? urlencode($config['username']) : null;
            $pass = $config['password'] ? urlencode($config['password']) : null;
            $auth = $user ? ($user . ($pass ? ':' . $pass : '') . '@') : '';
            $host = $config['host'] ?? 'localhost';
            $port = $config['port'] ?? ($scheme === 'smtps' ? 465 : 587);
            return "{$scheme}://{$auth}{$host}:{$port}";
        } elseif ($transport === 'sendmail') {
            return 'sendmail://default';
        } elseif ($transport === 'log') {
            return 'log://default';
        } elseif ($transport === 'in-memory') {
            return 'in-memory://default';
        }
        return null;
    }

    /**
     * Send a Mailable instance.
     */
    public function send(Mailable $mailable, ?string $mailerName = null): void
    {
        if ($mailable instanceof ShouldQueue) {
            $this->queue($mailable, $mailerName);
            return;
        }

        $this->sendNow($mailable, $mailerName);
    }

    /**
     * Send a Mailable instance immediately.
     */
    public function sendNow(Mailable $mailable, ?string $mailerName = null): void
    {
        try {
            $message = $mailable->buildSymfonyMessage($this);
            $this->getSymfonyMailer($mailerName)->send($message);
            Log::info('Email sent successfully', [
                'mailable' => get_class($mailable),
                'mailer' => $mailerName ?? $this->config['default'],
                'to' => array_map(fn($addr) => $addr->getAddress(), $mailable->to),
            ]);
        } catch (\Throwable $e) {
            Log::error('Email sending failed: ' . $e->getMessage(), [
                'exception' => $e,
                'mailable' => get_class($mailable),
                'mailer' => $mailerName ?? $this->config['default'],
            ]);
            throw new Exception("Failed to send email: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Queue a Mailable instance.
     */
    public function queue(Mailable $mailable, ?string $mailerName = null): void
    {
        Log::info('Queueing Mailable', [
            'mailable' => get_class($mailable),
            'mailer' => $mailerName ?? $this->config['default'],
        ]);
        // Placeholder: Implement queueing with a real queue system
        $this->sendNow($mailable, $mailerName); // Fallback until queue is implemented
    }

    /**
     * Render the HTML and Text content for a Mailable.
     */
    public function renderMailableContent(Mailable $mailable): array
    {
        $html = null;
        $text = null;

        if ($mailable->markdown) {
            $html = $this->renderMarkdownView($mailable->markdown, $mailable->viewData);
            if (!$mailable->textView) {
                $text = html_entity_decode(strip_tags(preg_replace('/<style.*?>.*?<\/style>/is', '', $html)));
            }
        } elseif ($mailable->view) {
            $html = $this->renderNiacView($mailable->view, $mailable->viewData);
        }

        if ($mailable->textView) {
            $text = $this->renderNiacView($mailable->textView, $mailable->viewData);
        }

        return [$html, $text];
    }

    /**
     * Render a Niac view.
     */
    public function renderNiacView(string $viewName, array $data): string
    {
        try {
            return $this->viewEngine->render($viewName, array_merge($data, [
                'mailer' => $this,
                'config' => $this->config,
            ]));
        } catch (Throwable $e) {
            Log::error('Niac mail view rendering failed', ['view' => $viewName, 'exception' => $e]);
            throw new Exception("Error rendering email view [{$viewName}]: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Render a Markdown view into HTML with theme.
     */
    public function renderMarkdownView(string $viewName, array $data): string
    {
        $markdownContent = $this->renderNiacView($viewName, $data);
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $bodyHtml = $converter->convert($markdownContent)->getContent();

        $theme = $this->config['markdown']['theme'] ?? 'modern';
        return $this->renderNiacView("vendor.mail.html.themes.{$theme}", [
            'body' => $bodyHtml,
            'mailer' => $this,
            'config' => $this->config,
        ]);
    }

    /**
     * Get the global 'from' address.
     */
    public function getGlobalFromAddress(): ?Address
    {
        return $this->globalFrom;
    }
}
