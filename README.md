Maniac Framework

The Maniac Framework is a lightweight, PHP-based web framework designed for rapid development with a focus on simplicity and flexibility. It includes a robust ORM, a Blade-like Niac templating engine, a powerful mailing system, and a notification system, all integrated with a modern console interface.

## Features

- **ORM (Model)**: A Laravel-like ORM with support for CRUD operations, relationships (`hasMany`, `belongsTo`, `belongsToMany`), and helper methods (`firstOrCreate`, `increment`, etc.).
- **Niac Templating Engine**: A Blade-inspired templating engine supporting layouts, sections, components, slots, and directives (`@if`, `@foreach`, `@component`, etc.).
- **Mailing System**: A robust email system with fluent interfaces, content components (`greeting`, `line`, `action`, etc.), and customizable themes (`default`, `modern`, `minimal`).
- **Notification System**: Supports multiple channels (`mail`, `database`) with queueing and flexible routing.
- **Console Commands**: Includes commands for migrations (`make:migration`, `migrate`), seeding (`make:seeder`, `db:seed`), and more.
- **Facades**: Provides a clean, expressive syntax for accessing services (`DB`, `QueryBuilder`, `Mail`, `Notification`).

## Requirements

- PHP &gt;= 8.1
- PDO extension for database connectivity
- Composer for dependency management
- MySQL or compatible database
- SMTP server or other mail transport for emailing

## Installation

1. **Clone the Repository**:

   ```bash
   git clone https://github.com/your-repo/maniac-framework.git
   cd maniac-framework
   ```

2. **Install Dependencies**:

   ```bash
   composer install
   ```

3. **Configure Environment**: Copy the `.env.example` to `.env` and update the settings:

   ```bash
   cp .env.example .env
   ```

   Update `.env` with your database and mail settings:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=maniac
   DB_USERNAME=root
   DB_PASSWORD=

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.example.com
   MAIL_PORT=587
   MAIL_USERNAME=your-username
   MAIL_PASSWORD=your-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=hello@example.com
   MAIL_FROM_NAME="Maniac Framework"
   ```

4. **Generate Application Key**:

   ```bash
   php maniac key:gen
   ```

5. **Run Migrations**:

   ```bash
   php maniac migrate
   ```

6. **Seed Database (Optional)**:

   ```bash
   php maniac db:seed
   ```

   Or seed a specific seeder:

   ```bash
   php maniac db:seed --class=UsersTableSeeder
   ```

7. **Serve the Application**:

   ```bash
   php maniac serve
   ```

   Access the application at `http://localhost:8001`.

## Directory Structure

```
maniac-framework/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   ├── Mail/
│   ├── Middlewares/
│   ├── Models/
│   ├── Notifications/
├── config/
├── core/
│   ├── App/
│   ├── Database/
│   ├── Foundation/
│   ├── Http/
│   ├── Logging/
│   ├── Mail/
│   ├── Mvc/
│   ├── Notifications/
│   ├── View/
├── database/
│   ├── migrations/
│   ├── seeders/
├── public/
├── resources/
│   ├── views/
│   │   ├── emails/
│   │   ├── vendor/
│   │   │   ├── mail/
├── storage/
│   ├── cache/
│   ├── logs/
├── .env
├── composer.json
├── maniac
```

## Mailing System

The mailing system is designed to be as robust and expressive as Laravel's. It uses the Niac templating engine and supports fluent interfaces, content components, and customizable themes.

### Creating a Mailable

Generate a new mailable:

```bash
php maniac make:mailable WelcomeEmail
```

Edit `app/Mail/WelcomeEmail.php`:

```php
<?php

namespace App\Mail;

use Core\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->from('no-reply@example.com', 'Maniac Framework')
                    ->to($this->user->email, $this->user->name)
                    ->subject('Welcome to Maniac Framework!')
                    ->markdown('emails.welcome')
                    ->with(['user' => $this->user])
                    ->greeting("Hello {$this->user->name}!")
                    ->line('Welcome to the Maniac Framework!')
                    ->action('Explore Dashboard', url('/dashboard'))
                    ->line('We are excited to have you on board.')
                    ->panel('Your account details: <br>Email: ' . $this->user->email)
                    ->table([
                        ['key' => 'Name', 'value' => $this->user->name],
                        ['key' => 'Email', 'value' => $this->user->email],
                    ], ['key', 'value'])
                    ->signature('The Maniac Team')
                    ->footer('© ' . date('Y') . ' Maniac Framework. All rights reserved.');
    }
}
```

Create the template `resources/views/emails/welcome.niac.php`:

```php
@component('vendor.mail.html.themes.modern', ['components' => $components])
    @if($user)
        Your custom content can go here.
    @endif
@endcomponent
```

### Sending an Email

Use the `Mail` facade:

```php
use Core\Mail\Mail;
use App\Mail\WelcomeEmail;

$user = (object) ['email' => 'user@example.com', 'name' => 'John Doe'];
Mail::to($user->email, $user->name)->send(new WelcomeEmail($user));
```

Or queue it:

```php
Mail::to($user->email, $user->name)->queue(new WelcomeEmail($user));
```

### Customizing Themes

Themes are located in `resources/views/vendor/mail/html/themes`. Available themes:

- `default`: Simple and clean design.
- `modern`: Sleek, professional look with gradients.
- `minimal`: Barebones, text-focused design.

To create a custom theme, add a new file (e.g., `custom.niac.php`) and update `config/mail.php`:

```php
'markdown' => [
    'theme' => 'custom',
    'paths' => [
        BASE_PATH . '/resources/views/vendor/mail',
    ],
],
```

### Content Components

The mailing system supports the following components:

- `greeting($text)`: A prominent heading (e.g., "Hello John!").
- `line($text)`: A paragraph of text.
- `action($text, $url)`: A call-to-action button.
- `panel($content)`: A highlighted content block.
- `table($data, $columns)`: A data table.
- `signature($text)`: A sign-off (e.g., "-- The Team").
- `footer($text)`: A footer note.

## Notification System

The notification system supports multiple channels (`mail`, `database`) and is extensible.

### Creating a Notification

Generate a new notification:

```bash
php maniac make:notification UserRegistered
```

Edit `app/Notifications/UserRegistered.php`:

```php
<?php

namespace App\Notifications;

use Core\Notifications\Notification;
use Core\Mail\Mailable;

class UserRegistered extends Notification
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): Mailable
    {
        return (new Mailable)
            ->to($notifiable->email)
            ->subject('Welcome to Maniac!')
            ->markdown('notifications.welcome')
            ->greeting("Hello {$this->user->name}!")
            ->line('Thank you for registering!')
            ->action('Login Now', url('/login'));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => "User {$this->user->name} registered.",
            'user_id' => $this->user->id,
            'created_at' => now(),
        ];
    }
}
```

### Sending a Notification

Use the `Notifiable` trait in your model:

```php
use Core\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;
}
```

Send the notification:

```php
$user = User::find(1);
$user->notify(new UserRegistered($user));
```

## Console Commands

Available commands:

```bash
php maniac list
```

Key commands:

- `make:migration`: Create a new migration.
- `migrate`: Run migrations.
- `make:seeder`: Create a new seeder.
- `db:seed`: Run seeders.
- `make:mailable`: Create a new mailable.
- `make:notification`: Create a new notification.
- `serve`: Start the development server.

## Extending the Framework

- **Custom Mailables**: Create new mailables in `app/Mail` and use custom Niac templates.
- **Custom Themes**: Add new themes to `resources/views/vendor/mail/html/themes`.
- **Custom Notifications**: Implement new channels by extending `ChannelInterface`.
- **Queue System**: Integrate a queue system (e.g., Redis, RabbitMQ) to replace the placeholder logic.

## Contributing

Contributions are welcome! Please submit pull requests or open issues on GitHub.

## License

Maniac Framework is open-sourced under the MIT License.
