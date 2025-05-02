<?php

namespace Core\App\Console\Commands;

use Core\Console\Command;

/**
 * Command to generate a new application key and update the .env file.
 */
class KeyGenerateCommand extends Command
{
    protected $name = 'key:gen';
    protected $description = 'Generate a new application key and update .env';

    public function handle(array $args): int
    {
        $cipher = env('APP_CIPHER', 'AES-256-GCM');
        if (!in_array($cipher, ['AES-128-GCM', 'AES-256-GCM'])) {
            $this->error("Invalid cipher '{$cipher}'. Supported ciphers: AES-128-GCM, AES-256-GCM");
            return 1;
        }
        $keyLength = ($cipher === 'AES-128-GCM') ? 16 : 32;

        try {
            $key = random_bytes($keyLength);
            $encodedKey = 'base64:' . base64_encode($key);
            $envFile = BASE_PATH . '/.env';

            if (!file_exists($envFile)) {
                if (!touch($envFile)) {
                    $this->error("Could not create .env file at {$envFile}");
                    return 1;
                }
                $this->info("Created new .env file at {$envFile}");
            }

            $envContent = file_get_contents($envFile);
            if ($envContent === false) {
                $this->error("Could not read .env file at {$envFile}");
                return 1;
            }

            if (preg_match('/^APP_KEY=/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$encodedKey}", $envContent);
            } else {
                $envContent = rtrim($envContent, "\n") . "\nAPP_KEY={$encodedKey}\n";
            }

            if (file_put_contents($envFile, $envContent) === false) {
                $this->error("Could not write to .env file at {$envFile}");
                return 1;
            }

            $this->info("Generated new APP_KEY for {$cipher} and updated .env file:");
            $this->info("APP_KEY={$encodedKey}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to generate key or update .env file: {$e->getMessage()}");
            return 1;
        }
    }
}
