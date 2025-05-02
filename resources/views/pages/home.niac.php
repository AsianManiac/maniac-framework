@extends('layouts.app') {{-- Extend the main layout --}}

@section('title', 'Homepage') {{-- Set the title for this page --}}

@section('content') {{-- Define the main content section --}}
<div class="bg-white shadow rounded-lg p-6">
    <h1 class="text-3xl font-bold text-blue-600 mb-4">Welcome to Maniac!</h1>

    <p class="mb-4">This is the homepage rendered using the Niac templating engine.</p>

    <!--- Example: Looping with @foreach --->
    @isset($users)
    <h2 class="text-xl font-semibold mb-2">Users:</h2>
    @if(count($users) > 0)
    <ul class="list-disc list-inside mb-4">
        @foreach($users as $user)
        <li>{{ $user->name }} - {{ $user->email }}</li>
        @endforeach
    </ul>
    @else
    <p>No users found.</p>
    @endif
    @endisset

    <!--- Example: Conditional with @if/@else --->
    @if(config('app.debug'))
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 my-4" role="alert">
        <p class="font-bold">Debug Mode</p>
        <p>Application is currently in debug mode.</p>
    </div>
    @else
    <p class="text-sm text-gray-500">Application is in production mode.</p>
    @endif

    <!--- Example: Raw HTML Output --->
    <div class="mt-4">
        {!! $rawHtmlVariable ?? '<!-- No raw HTML provided -->' !!}
    </div>

    <!--- Example: Including PHP --->
    @php
    $timestamp = date('Y-m-d H:i:s');
    @endphp
    <p class="text-xs text-gray-400 mt-6">Page rendered at: {{ $timestamp }}</p>

    {{-- Example CSRF Field --}}
    {{-- <form method="POST" action="/some-action">
             @csrf
             <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Submit</button>
         </form> --}}

</div>
@endsection

@section('scripts') {{-- Add page-specific scripts --}}
<script>
    console.log('Home page specific script loaded!');
</script>
@endsection
