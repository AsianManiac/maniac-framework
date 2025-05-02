@extends('layouts.app')

@title('500 - Server Error')
@section('content')
<div class="max-w-md mx-auto text-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-5xl font-bold text-blue-600 mb-6">500</div>
    <h1 class="text-3xl font-extrabold text-gray-900 mb-4">Server Error</h1>
    <p class="text-lg text-gray-600 mb-8">Something went wrong on our end. We're working to fix it.</p>
    <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        Go back home
    </a>
    <a href="/contact" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        Contact Support
    </a>
</div>
@endsection
