<!DOCTYPE html>
<html lang="{{ $app_locale ?? 'en' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $__engine->getTitle(config('app.name', 'Maniac Framework')) }}</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom styles */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .nav-link {
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: #3b82f6;
            color: white;
            transform: translateY(-2px);
        }

        .footer-link {
            transition: all 0.2s ease;
        }

        .footer-link:hover {
            color: #3b82f6;
            transform: translateX(2px);
        }

        .content-wrapper {
            flex: 1;
        }
    </style>

</head>

<body class="bg-gray-50">
    @include('partials.header')

    <div>
        <main>
            <div>
                <div>
                    <h1>
                        Welcome to <span>Maniac Framework</span>
                    </h1>
                    <p>
                        A lightweight PHP framework for building modern web applications.
                    </p>
                </div>

            </div>
        </main>
    </div>


</body>

</html>
