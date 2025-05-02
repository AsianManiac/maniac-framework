<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Maniac Framework</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f8f8f8;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            background: #e3342f;
            color: #fff;
            padding: 15px;
            margin: -20px -20px 20px;
            border-radius: 8px 8px 0 0;
            font-size: 24px;
        }

        .error-details {
            margin-bottom: 20px;
        }

        .error-details p {
            margin: 5px 0;
        }

        .error-details strong {
            color: #444;
        }

        .stack-trace {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            font-family: Consolas, monospace;
            white-space: pre-wrap;
            font-size: 14px;
        }

        .stack-trace::before {
            content: 'Stack Trace';
            display: block;
            color: #ff79c6;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Error: {{ $error['message'] }}</h1>
        <div class="error-details">
            <p><strong>File:</strong> {{ $error['file'] }}</p>
            <p><strong>Line:</strong> {{ $error['line'] }}</p>
        </div>
        <div class="stack-trace">
            {{ $error['trace'] }}
        </div>
    </div>
</body>

</html>
