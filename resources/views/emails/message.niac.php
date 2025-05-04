<!-- resources/views/vendor/mail/text/message.niac.php -->
@foreach($components as $component)
@if($component['type'] === 'greeting')
{{ $component['value'] }}

@elseif($component['type'] === 'line')
{{ $component['value'] }}

@elseif($component['type'] === 'action')
{{ $component['value']['text'] }}: {{ $component['value']['url'] }}

@elseif($component['type'] === 'panel')
{{ strip_tags($component['value']) }}

@elseif($component['type'] === 'table')
@foreach($component['value']['data'] as $row)
@foreach($component['value']['columns'] as $key)
{{ $key }}: {{ $row[$key] ?? '' }}
@endforeach
-----
@endforeach

@elseif($component['type'] === 'signature')
--
{{ $component['value'] }}

@elseif($component['type'] === 'footer')
{{ $component['value'] }}
@endif
@endforeach
{{ strip_tags($body ?? '') }}
