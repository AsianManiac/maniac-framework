@extends('layouts.app')

@title('503 - Service Unavailable')
@section('content')
<div class="max-w-md mx-auto text-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-5xl font-bold text-blue-600 mb-6">503</div>
    <h1 class="text-3xl font-extrabold text-gray-900 mb-4">Service Unavailable</h1>
    <p class="text-lg text-gray-600 mb-8">We're down for maintenance. Please check back soon.</p>
    <div class="animate-pulse bg-blue-100 rounded-lg p-4 mb-6">
        <p class="text-blue-800">Expected back: {{ date('F j, Y H:i', strtotime('+1 hour')) }}</p>
    </div>
    <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        Refresh
    </a>
</div>
@endsection
