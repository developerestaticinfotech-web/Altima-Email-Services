<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Processed - {{ $broker['name'] ?? 'ForexPro' }}</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .transaction-details {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Withdrawal Processed</h1>
        <p>Your funds are on their way</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user['name'] ?? 'Valued Customer' }},</h2>
        
        <p>Your withdrawal request has been successfully processed and your funds are on their way to your account.</p>
        
        <div class="transaction-details">
            <h3>Transaction Details:</h3>
            <p><strong>Transaction ID:</strong> {{ $transaction['id'] ?? 'N/A' }}</p>
            <p><strong>Amount:</strong> <span class="amount">{{ $transaction['amount'] ?? '0.00' }} {{ $transaction['currency'] ?? 'USD' }}</span></p>
            <p><strong>Status:</strong> <span class="status-badge">{{ $transaction['status'] ?? 'Processed' }}</span></p>
            <p><strong>Processing Date:</strong> {{ $transaction['processed_at'] ?? now()->format('F j, Y H:i:s') }}</p>
            <p><strong>Destination:</strong> {{ $transaction['destination'] ?? 'Your registered account' }}</p>
        </div>
        
        <p><strong>Important Information:</strong></p>
        <ul>
            <li>Processing time: 1-3 business days</li>
            <li>You will receive a confirmation when funds arrive</li>
            <li>Keep this email for your records</li>
        </ul>
        
        <p>If you have any questions about this transaction, please contact our support team at <a href="mailto:{{ $broker['support_email'] ?? 'support@forexpro.com' }}">{{ $broker['support_email'] ?? 'support@forexpro.com' }}</a>.</p>
        
        <p>Thank you for choosing {{ $broker['name'] ?? 'ForexPro' }}!</p>
        
        <p>Best regards,<br>
        The {{ $broker['name'] ?? 'ForexPro' }} Team</p>
    </div>
    
    <div class="footer">
        <p>This email was sent to {{ $user['email'] ?? 'you' }} on {{ now()->format('F j, Y H:i:s') }}.</p>
        <p>© {{ date('Y') }} {{ $broker['name'] ?? 'ForexPro' }}. All rights reserved.</p>
        <p><a href="{{ url('/unsubscribe') }}">Unsubscribe</a> | <a href="{{ url('/privacy') }}">Privacy Policy</a></p>
    </div>
</body>
</html> 