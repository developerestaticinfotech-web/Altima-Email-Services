<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
        .body-content {
            white-space: pre-wrap;
            font-size: 16px;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 style="margin: 0; font-size: 24px;">{{ $subject }}</h1>
        </div>
        
        <div class="content">
            <div class="body-content">{{ $bodyContent }}</div>
        </div>
        
        <div class="footer">
            <p style="margin: 0;">This email was sent via the RabbitMQ queue system.</p>
            <p style="margin: 5px 0 0 0;">Sent at: {{ $sentAt }}</p>
        </div>
    </div>
</body>
</html>
