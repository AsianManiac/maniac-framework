<?php

use App\Models\User;
use App\Models\Invoice;
use App\Notifications\InvoicePaid;
use Core\Notifications\Notification;

$invoice = User::find(5);
$user = User::find($invoice->user_id);

// Option 1: Using the Notifiable trait on the User model
// $user->notify(new InvoicePaid($invoice)); // Will be queued if implements ShouldQueue

// Option 2: Using the Notification Facade
// Notification::send($user, new InvoicePaid($invoice));

// Option 3: Sending to multiple users
// $users = User::where('is_admin', true)->get();
// Notification::send($users, new InvoicePaid($invoice));

// Option 4: On-demand notification (e.g., sending to an email address directly)
// Notification::route('mail', 'customer@example.com')
//             ->notify(new InvoicePaid($invoice));
