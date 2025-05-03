<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $config['from']['name'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f8f8; padding: 10px; text-align: center; }
        .content { padding: 20px; background: #fff; }
        .button { display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
        .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $config['from']['name'] }}</h1>
        </div>
        <div class="content">
            {{ $body }}
        </div>
        <div class="footer">
            © {{ now()->year }} {{ $config['from']['name'] }}. All rights reserved.
        </div>
    </div>
</body>
</html>