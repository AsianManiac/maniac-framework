<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;
use Core\Logging\Log;

/**
 * Command to generate a new notification class.
 */
class MakeNotificationCommand extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'make:notification';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a new notification class';

    /**
     * Execute the command.
     *
     * @param array $args The command-line arguments
     * @return int 0 for success, 1 for failure
     */
    public function handle(array $args): int
    {
        if (empty($args[0])) {
            $this->error('Please provide a notification name.');
            return 1;
        }

        $name = $args[0];
        $namespace = 'App\\Notifications';
        // $className = class_basename($name);
        $fileName = BASE_PATH . '/app/Notifications/' . $name . '.php';

        // Check if file already exists
        if (file_exists($fileName)) {
            $this->error("Notification {$name} already exists at {$fileName}.");
            return 1;
        }

        // Create directory if it doesn't exist
        $directory = dirname($fileName);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate notification template
        $template = <<<EOT
<?php

namespace {$namespace};

use Core\Notifications\Notification;
use Core\Mail\Mailable;

class {$name} extends Notification
{
    public function __construct()
    {
        //
    }

    public function via(\$notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(\$notifiable): Mailable
    {
        return (new Mailable)
            ->to(\$notifiable->email)
            ->subject('{$name}')
            ->markdown('notifications.{$name}')
            ->greeting('Hello!')
            ->line('This is a new notification.');
    }

    public function toDatabase(\$notifiable): array
    {
        return [
            'message' => '{$name} triggered.',
            'created_at' => now(),
        ];
    }
}
EOT;

        // Write the file
        try {
            file_put_contents($fileName, $template);
            $this->info("Notification created successfully: {$fileName}");
            return 0;
        } catch (\Exception $e) {
            Log::error("Failed to create notification: {$e->getMessage()}", ['exception' => $e]);
            $this->error("Failed to create notification: {$e->getMessage()}");
            return 1;
        }
    }
}
