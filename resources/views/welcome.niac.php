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

    <div class="content-wrapper">
        <main class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
                        Welcome to <span class="text-blue-600">Maniac Framework</span>
                    </h1>
                    <p class="mt-5 max-w-xl mx-auto text-xl text-gray-500">
                        A lightweight PHP framework for building modern web applications.
                    </p>
                </div>

                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-8 sm:p-10">
                        <div class="prose prose-blue max-w-none">
                            <h2 class="text-2xl font-bold text-gray-900">Getting Started</h2>
                            <p class="mt-4 text-gray-600">
                                Start building your application with our comprehensive documentation and examples.
                            </p>


                            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <a href="/docs" class="flex items-start p-6 bg-white rounded-lg border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all duration-300">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-book text-blue-500 text-2xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Documentation</h3>
                                        <p class="mt-1 text-gray-500">Learn how to use Maniac Framework</p>
                                    </div>
                                </a>

                                <a href="/examples" class="flex items-start p-6 bg-white rounded-lg border border-gray-200 hover:border-blue-500 hover:shadow-md transition-all duration-300">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-code text-blue-500 text-2xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Examples</h3>
                                        <p class="mt-1 text-gray-500">See practical examples and snippets</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>


</body>

</html>
