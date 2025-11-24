Withdrawal Processed - {{ $broker['name'] ?? 'ForexPro' }}

Hello {{ $user['name'] ?? 'Valued Customer' }},

Your withdrawal request has been successfully processed and your funds are on their way to your account.

Transaction Details:
- Transaction ID: {{ $transaction['id'] ?? 'N/A' }}
- Amount: {{ $transaction['amount'] ?? '0.00' }} {{ $transaction['currency'] ?? 'USD' }}
- Status: {{ $transaction['status'] ?? 'Processed' }}
- Processing Date: {{ $transaction['processed_at'] ?? now()->format('F j, Y H:i:s') }}
- Destination: {{ $transaction['destination'] ?? 'Your registered account' }}

Important Information:
1. Processing time: 1-3 business days
2. You will receive a confirmation when funds arrive
3. Keep this email for your records

If you have any questions about this transaction, please contact our support team at {{ $broker['support_email'] ?? 'support@forexpro.com' }}.

Thank you for choosing {{ $broker['name'] ?? 'ForexPro' }}!

Best regards,
The {{ $broker['name'] ?? 'ForexPro' }} Team

---
This email was sent to {{ $user['email'] ?? 'you' }} on {{ now()->format('F j, Y H:i:s') }}.
© {{ date('Y') }} {{ $broker['name'] ?? 'ForexPro' }}. All rights reserved.
Unsubscribe: {{ url('/unsubscribe') }}
Privacy Policy: {{ url('/privacy') }} 