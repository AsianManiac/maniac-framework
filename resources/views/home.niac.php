@extends('layouts.app')

@title('Home - Maniac Framework')
@section('content')
<h1>Home Page</h1>
<p>This is the home page content.</p>
@isset($users)
<ul>
    @foreach($users as $user)
    <li>{{ $user['name'] ?? 'N/A' }}</li>
    @endforeach
</ul>
@else
<p>No users available.</p>
@endisset
@endsection
