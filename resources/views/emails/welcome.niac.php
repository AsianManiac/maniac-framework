@component('mail::message')
# Welcome, {{ $name }}!

Thank you for joining the Maniac Framework.

@component('mail::button', ['url' => 'http://localhost:8000'])
Get Started
@endcomponent

Best regards,
The Maniac Team
@endcomponent
