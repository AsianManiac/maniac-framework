<?php

namespace Core\Notifications\Channels;

use Core\Database\DB;
use Core\Logging\Log;
use Illuminate\Support\Carbon;
use Core\Notifications\Notification;

class DatabaseChannel implements ChannelInterface
{
    /**
     * Send the given notification. Stores it in the database.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        $data = $this->getData($notifiable, $notification);

        // Get the notifiable ID and type (assuming Eloquent-like structure)
        // This needs adjustment based on your actual ORM/Model structure
        if (!method_exists($notifiable, 'getKey') || !method_exists($notifiable, 'getMorphClass')) {
            // Cannot store notification if we can't identify the notifiable
            Log::error("Cannot store database notification: Notifiable entity is missing required methods (getKey, getMorphClass).");
            return;
        }
        $notifiableId = $notifiable->getKey();
        $notifiableType = $notifiable->getMorphClass(); // E.g., App\Models\User

        // --- Database Interaction ---
        // Assumes a 'notifications' table and a way to insert data
        // Replace with your DBAL, Query Builder or ORM usage
        try {
            DB::table('notifications')->insert([ // Example using a DB facade/helper
                'id' => $notification->id, // Use the generated UUID
                'type' => get_class($notification),
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
                'data' => json_encode($data), // Store data as JSON
                'read_at' => null,
                'created_at' => new \DateTime(), // Use Carbon or DateTime
                'updated_at' => new \DateTime(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to store database notification.", ['exception' => $e]);
        }
    }

    /**
     * Get the data for the notification.
     */
    protected function getData(mixed $notifiable, Notification $notification): array
    {
        // Prefer toDatabase method, fallback to toArray
        if (method_exists($notification, 'toDatabase')) {
            return $notification->toDatabase($notifiable);
        }
        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }
        return []; // No data representation found
    }
}
