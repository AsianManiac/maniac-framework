<footer class="bg-gray-900 text-gray-300 py-8 mt-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-xl font-bold text-white mb-4">{{ config('app.name', 'Maniac Framework') }}</h3>
                <p class="text-gray-400">A lightweight PHP framework for building modern web applications.</p>
            </div>

            <div>
                <h4 class="text-lg font-semibold text-white mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="/docs" class="footer-link hover:text-blue-400">Documentation</a></li>
                    <li><a href="/blog" class="footer-link hover:text-blue-400">Blog</a></li>
                    <li><a href="/status" class="footer-link hover:text-blue-400">Status</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-lg font-semibold text-white mb-4">Legal</h4>
                <ul class="space-y-2">
                    <li><a href="/privacy" class="footer-link hover:text-blue-400">Privacy Policy</a></li>
                    <li><a href="/terms" class="footer-link hover:text-blue-400">Terms of Service</a></li>
                    <li><a href="/license" class="footer-link hover:text-blue-400">License</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 text-sm mb-4 md:mb-0">
                Copyright &copy; {{ date('Y') }} {{ config('app.name', 'Maniac Framework') }}. All rights reserved.
            </p>

            <div class="flex space-x-6">
                <a href="https://twitter.com" class="text-gray-400 hover:text-blue-400 transition-colors duration-300">
                    <i class="fab fa-twitter fa-lg"></i>
                </a>
                <a href="https://github.com" class="text-gray-400 hover:text-gray-300 transition-colors duration-300">
                    <i class="fab fa-github fa-lg"></i>
                </a>
                <a href="https://linkedin.com" class="text-gray-400 hover:text-blue-600 transition-colors duration-300">
                    <i class="fab fa-linkedin fa-lg"></i>
                </a>
            </div>
        </div>
    </div>
</footer>
