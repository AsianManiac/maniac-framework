<?php
namespace Core\Logging\Drivers;

use Exception;
use Psr\Log\LogLevel;
use DateTimeInterface;
use Psr\Log\AbstractLogger;

class FileLogger extends AbstractLogger {
    protected string $path;
    protected string $minLevel;
    protected array $levelMap = [ // Map PSR levels to severity integers
        LogLevel::DEBUG     => 100,
        LogLevel::INFO      => 200,
        LogLevel::NOTICE    => 250,
        LogLevel::WARNING   => 300,
        LogLevel::ERROR     => 400,
        LogLevel::CRITICAL  => 500,
        LogLevel::ALERT     => 550,
        LogLevel::EMERGENCY => 600,
    ];

    public function __construct(string $path, string $minLevel = LogLevel::DEBUG) {
        $this->path = $path;
        $this->minLevel = $minLevel;

        // Ensure log directory exists
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            // Attempt to create directory recursively
            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                 // Throw error only if directory creation failed AND it doesn't exist
                 throw new Exception("Log directory could not be created: {$dir}");
            }
        }
         // Ensure log file is writable (or can be created)
         if (file_exists($this->path) && !is_writable($this->path)) {
              throw new Exception("Log file is not writable: {$this->path}");
         } elseif (!file_exists($this->path) && !is_writable($dir)) {
              throw new Exception("Log directory is not writable, cannot create log file: {$dir}");
         }
    }

    public function log($level, string|\Stringable $message, array $context = []): void {
        if ($this->levelMap[$level] < $this->levelMap[$this->minLevel]) {
            return; // Skip logging if below minimum level
        }

        $message = $this->formatMessage($level, (string) $message, $context);
        file_put_contents($this->path, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    protected function formatMessage(string $level, string $message, array $context): string {
         $timestamp = date(DateTimeInterface::RFC3339_EXTENDED); // More precise timestamp
         $level = strtoupper($level);
         $interpolatedMessage = $this->interpolate($message, $context);

         $logEntry = "[{$timestamp}] [{$level}] {$interpolatedMessage}";

         // Include exception details if present in context
         if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
             $exception = $context['exception'];
             $logEntry .= "\nException: " . get_class($exception);
             $logEntry .= "\nMessage: " . $exception->getMessage();
             $logEntry .= "\nFile: " . $exception->getFile() . ":" . $exception->getLine();
             $logEntry .= "\nStack trace:\n" . $exception->getTraceAsString();
             // Remove exception from context so it's not duplicated below
             unset($context['exception']);
         }

         // Append remaining context if any
         if (!empty($context)) {
              // Use JSON_UNESCAPED_SLASHES and JSON_UNESCAPED_UNICODE for better readability
              $contextString = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
              if ($contextString !== false && $contextString !== '[]' && $contextString !== '{}') {
                   $logEntry .= "\nContext: " . $contextString;
              }
         }

         return $logEntry;
    }

    /**
     * Interpolates context values into the message placeholders.
     * Basic implementation from PSR-3 recommendation.
     */
    protected function interpolate(string $message, array $context): string {
        $replace = [];
        foreach ($context as $key => $val) {
            // Check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }
}
