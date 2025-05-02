<?php

namespace Core\Notifications;

use Closure;
use Exception;
use Core\Logging\Log;
use Core\Notifications\Notification;
use Core\Contracts\Queue\ShouldQueue;
use Core\Notifications\Channels\MailChannel;
use Core\Notifications\Channels\DatabaseChannel;
use Core\Notifications\Channels\ChannelInterface;

class NotificationSender
{
    protected array $channels = []; // Resolved channel instances

    public function __construct()
    {
        // Register default channel drivers (can be made configurable)
        $this->registerChannel('mail', fn() => app(MailChannel::class));
        $this->registerChannel('database', fn() => app(DatabaseChannel::class));
        // Register other channels...
    }

    /**
     * Register a custom channel driver resolver.
     */
    public function registerChannel(string $name, Closure $resolver): void
    {
        $this->channels[$name] = $resolver;
    }

    /**
     * Send the given notification to the given notifiable entities.
     *
     * @param mixed $notifiables Entity or collection of entities.
     * @param Notification $notification
     * @return void
     */
    public function send(mixed $notifiables, Notification $notification): void
    {
        $notifiables = $this->formatNotifiables($notifiables);

        if ($notification instanceof ShouldQueue) {
            $this->queueNotification($notifiables, $notification);
            return;
        }

        $this->sendNow($notifiables, $notification);
    }

    /**
     * Send the given notification immediately.
     *
     * @param mixed $notifiables
     * @param Notification $notification
     * @return void
     */
    public function sendNow(mixed $notifiables, Notification $notification): void
    {
        $notifiables = $this->formatNotifiables($notifiables);
        $viaChannels = $notification->via(null); // Get initial channels first

        foreach ($notifiables as $notifiable) {
            // Get channels specific to this notifiable if via() uses it
            $channels = $notification->via($notifiable); // Allow per-notifiable channels
            if (empty($channels)) continue;

            foreach ($channels as $channelName) {
                try {
                    $channelInstance = $this->resolveChannel($channelName);
                    if ($channelInstance) {
                        $channelInstance->send($notifiable, $notification);
                    } else {
                        Log::warning("Notification channel [{$channelName}] not found or resolvable.");
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to send notification via channel [{$channelName}]", [
                        'exception' => $e,
                        'notification' => get_class($notification),
                        'notifiable_type' => get_class($notifiable),
                        // 'notifiable_id' => $notifiable->getKey(), // If model has getKey()
                    ]);
                    // Optionally re-throw or handle based on app needs
                }
            }
        }
    }

    /**
     * Queue the notification.
     * (Placeholder - needs Queue system)
     */
    protected function queueNotification(array $notifiables, Notification $notification): void
    {
        Log::info('Queueing Notification', [
            'notification' => get_class($notification),
            'notifiable_count' => count($notifiables)
        ]);
        // Real implementation:
        // Queue::push(new SendQueuedNotifications($notifiables, $notification));
        // Fallback to sync for now:
        $this->sendNow($notifiables, $notification); // REMOVE WHEN QUEUE IS REAL
    }

    /**
     * Resolve a channel instance by name.
     */
    protected function resolveChannel(string $name): ?ChannelInterface
    {
        if (!isset($this->channels[$name])) return null;

        $resolver = $this->channels[$name];
        $instance = $resolver(); // Call the closure factory

        if (!$instance instanceof ChannelInterface) {
            throw new Exception("Resolver for channel [{$name}] did not return an instance of ChannelInterface.");
        }
        return $instance;
    }

    /**
     * Format the notifiables into an array.
     */
    protected function formatNotifiables(mixed $notifiables): array
    {
        if (is_array($notifiables) || $notifiables instanceof \Traversable) {
            return is_array($notifiables) ? $notifiables : iterator_to_array($notifiables);
        }
        return [$notifiables]; // Wrap single entity in array
    }
}
