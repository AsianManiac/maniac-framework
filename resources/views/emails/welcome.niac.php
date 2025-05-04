<!-- resources/views/emails/welcome.niac.php -->
@component('vendor.mail.html.themes.modern', ['components' => $components, 'user' => $user ?? null])
@if(isset($user))
Your custom content can go here.
<p>Welcome, {{ $user->name ?? 'Guest' }}!</p>
@else
<p>Welcome, Guest!</p>
@endif
@endcomponent
