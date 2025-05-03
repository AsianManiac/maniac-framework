<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;
use Core\Logging\Log;

/**
 * Command to generate a new mailable class.
 */
class MakeMailableCommand extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'make:mailable';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a new mailable class';

    /**
     * Execute the command.
     *
     * @param array $args The command-line arguments
     * @return int 0 for success, 1 for failure
     */
    public function handle(array $args): int
    {
        if (empty($args[0])) {
            $this->error('Please provide a mailable name.');
            return 1;
        }

        $name = $args[0];
        $namespace = 'App\\Mail';
        // $className = class_basename($name);
        $fileName = BASE_PATH . '/app/Mail/' . $name . '.php';

        // Check if file already exists
        if (file_exists($fileName)) {
            $this->error("Mailable {$name} already exists at {$fileName}.");
            return 1;
        }

        // Create directory if it doesn't exist
        $directory = dirname($fileName);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate mailable template
        $template = <<<EOT
<?php

namespace {$namespace};

use Core\Mail\Mailable;

class {$name} extends Mailable
{
    public function __construct()
    {
        //
    }

    public function build()
    {
        return \$this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('{$name}')
                    ->markdown('emails.{$name}')
                    ->with([]);
    }
}
EOT;

        // Write the file
        try {
            file_put_contents($fileName, $template);
            $this->info("Mailable created successfully: {$fileName}");
            return 0;
        } catch (\Exception $e) {
            Log::error("Failed to create mailable: {$e->getMessage()}", ['exception' => $e]);
            $this->error("Failed to create mailable: {$e->getMessage()}");
            return 1;
        }
    }
}
