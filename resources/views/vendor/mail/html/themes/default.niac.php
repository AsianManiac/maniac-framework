<!-- resources/views/vendor/mail/html/themes/default.niac.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ 'Maniac Framework' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #007bff;
            color: #fff;
            border-radius: 8px 8px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 20px;
        }

        .action {
            text-align: center;
            margin: 20px 0;
        }

        .action a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .panel {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
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
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f4f4f4;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            padding: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $mailer->getTitle('Maniac Framework') }}</h1>
        </div>
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
