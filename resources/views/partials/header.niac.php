<header class="bg-gray-900 text-white shadow-lg">
    <div class="container mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center">
        <div class="flex items-center">
            <h1 class="text-2xl font-bold text-blue-400 hover:text-blue-300 transition-colors duration-300">
                <a href="/">{{ config('app.name', 'Maniac') }}</a>
            </h1>
        </div>

        <nav class="mt-4 md:mt-0">
            <ul class="flex flex-wrap justify-center gap-2 md:gap-4">
                <li><a href="/" class="px-4 py-2 rounded nav-link font-medium {{ request()->is('/') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-blue-700' }}">Home</a></li>
                <li><a href="/users" class="px-4 py-2 rounded nav-link font-medium {{ request()->is('users') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-blue-700' }}">Users</a></li>
                <li><a href="/about" class="px-4 py-2 rounded nav-link font-medium {{ request()->is('about') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-blue-700' }}">About</a></li>
                <li><a href="/contact" class="px-4 py-2 rounded nav-link font-medium {{ request()->is('contact') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-blue-700' }}">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>
