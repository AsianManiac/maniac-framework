<!-- resources/views/vendor/mail/html/themes/minimal.niac.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ 'Maniac Framework' }}</title>
    <style>
        body {
            font-family: 'Verdana', sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: #fff;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 580px;
            margin: 0 auto;
        }

        .content {
            padding: 10px 0;
        }

        .action {
            text-align: center;
            margin: 15px 0;
        }

        .action a {
            display: inline-block;
            padding: 8px 16px;
            background: #555;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .panel {
            padding: 10px;
            border: 1px solid #ddd;
            margin: 10px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .table th {
            background: #f5f5f5;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            @foreach($components as $component)
            @if($component['type'] === 'greeting')
            <h2>{{ $component['value'] }}</h2>
            @elseif($component['type'] === 'line')
            <p>{{ $component['value'] }}</p>
            @elseif($component['type'] === 'action')
            <div class="action">
                <a href="{{ $component['value']['url'] }}">{{ $component['value']['text'] }}</a>
            </div>
            @elseif($component['type'] === 'panel')
            <div class="panel">{!! $component['value'] !!}</div>
            @elseif($component['type'] === 'table')
            <table class="table">
                <thead>
                    <tr>
                        @foreach($component['value']['columns'] as $column)
                        <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($component['value']['data'] as $row)
                    <tr>
                        @foreach($component['value']['columns'] as $key)
                        <td>{{ $row[$key] ?? '' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @elseif($component['type'] === 'signature')
            <p>-- <br>{{ $component['value'] }}</p>
            @elseif($component['type'] === 'footer')
            <div class="footer">{{ $component['value'] }}</div>
            @endif
            @endforeach
            {{ $body ?? '' }}
        </div>
    </div>
</body>

</html>
