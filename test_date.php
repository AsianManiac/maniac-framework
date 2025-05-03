<?php

// Bootstrap the application for testing
require __DIR__ . '/bootstrap/test.php';

use Core\Mail\Mail;
use App\Mail\WelcomeEmail;

$user = (object) ['email' => 'test@example.com', 'name' => 'Test User'];
Mail::to($user->email, $user->name)->send(new WelcomeEmail($user));

echo "Mail sent successfully.\n";
