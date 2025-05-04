<!-- resources/views/vendor/mail/html/themes/modern.niac.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ 'Maniac Framework' }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.8;
            color: #2d3748;
            background-color: #edf2f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 640px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #3182ce, #2b6cb0);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .content {
            padding: 30px;
        }

        .action {
            text-align: center;
            margin: 20px 0;
        }

        .action a {
            display: inline-block;
            padding: 12px 24px;
            background: #3182ce;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .action a:hover {
            background: #2b6cb0;
        }

        .panel {
            background: #f7fafc;
            padding: 20px;
            border-left: 5px solid #3182ce;
            border-radius: 6px;
            margin: 15px 0;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 15px 0;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
        }

        .table th {
            background: #edf2f7;
            font-weight: 600;
            color: #2d3748;
        }

        .table td {
            border-bottom: 1px solid #e2e8f0;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #718096;
            padding: 20px;
            background: #f7fafc;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $__engine->getTitle('Maniac Framework') }}</h1>
        </div>
        <div class="content">
            @foreach($components as $component)
            @if($component['type'] === 'greeting')
            <h2 style="font-size: 24px; color: #2d3748;">{{ $component['value'] }}</h2>
            @elseif($component['type'] === 'line')
            <p style="margin: 10px 0;">{{ $component['value'] }}</p>
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
            <p style="margin: 20px 0;">-- <br>{{ $component['value'] }}</p>
            @elseif($component['type'] === 'footer')
            <div class="footer">{{ $component['value'] }}</div>
            @endif
            @endforeach
            {{ $body ?? '' }}
        </div>
    </div>
</body>

</html>
