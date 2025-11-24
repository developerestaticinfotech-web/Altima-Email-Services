<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $broker['name'] ?? 'ForexPro' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .account-details {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to {{ $broker['name'] ?? 'ForexPro' }}!</h1>
        <p>Your trading journey starts now</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user['name'] ?? 'Valued Customer' }},</h2>
        
        <p>Welcome to {{ $broker['name'] ?? 'ForexPro' }}! We're excited to have you on board and can't wait to help you achieve your trading goals.</p>
        
        <div class="account-details">
            <h3>Your Account Details:</h3>
            <p><strong>Account Type:</strong> {{ $account['type'] ?? 'Standard' }}</p>
            <p><strong>Leverage:</strong> {{ $account['leverage'] ?? '1:100' }}</p>
            <p><strong>Currency:</strong> {{ $account['currency'] ?? 'USD' }}</p>
            <p><strong>Referral Code:</strong> <code>{{ $user['referral_code'] ?? 'N/A' }}</code></p>
        </div>
        
        <p>Your account has been successfully created and is ready for trading. Here's what you can do next:</p>
        
        <ul>
            <li>Complete your profile verification</li>
            <li>Fund your account</li>
            <li>Download our trading platform</li>
            <li>Start exploring the markets</li>
        </ul>
        
        <a href="{{ $broker['website'] ?? 'https://forexpro.com' }}" class="button">Get Started</a>
        
        <p>If you have any questions or need assistance, our support team is available 24/7 at <a href="mailto:{{ $broker['support_email'] ?? 'support@forexpro.com' }}">{{ $broker['support_email'] ?? 'support@forexpro.com' }}</a>.</p>
        
        <p>Happy trading!</p>
        
        <p>Best regards,<br>
        The {{ $broker['name'] ?? 'ForexPro' }} Team</p>
    </div>
    
    <div class="footer">
        <p>This email was sent to {{ $user['email'] ?? 'you' }} on {{ $user['signup_date'] ?? now()->format('F j, Y') }}.</p>
        <p>© {{ date('Y') }} {{ $broker['name'] ?? 'ForexPro' }}. All rights reserved.</p>
        <p><a href="{{ url('/unsubscribe') }}">Unsubscribe</a> | <a href="{{ url('/privacy') }}">Privacy Policy</a></p>
    </div>
</body>
</html> 