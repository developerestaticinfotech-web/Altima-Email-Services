Welcome to {{ $broker['name'] ?? 'ForexPro' }}!

Hello {{ $user['name'] ?? 'Valued Customer' }},

Welcome to {{ $broker['name'] ?? 'ForexPro' }}! We're excited to have you on board and can't wait to help you achieve your trading goals.

Your Account Details:
- Account Type: {{ $account['type'] ?? 'Standard' }}
- Leverage: {{ $account['leverage'] ?? '1:100' }}
- Currency: {{ $account['currency'] ?? 'USD' }}
- Referral Code: {{ $user['referral_code'] ?? 'N/A' }}

Your account has been successfully created and is ready for trading. Here's what you can do next:

1. Complete your profile verification
2. Fund your account
3. Download our trading platform
4. Start exploring the markets

Get Started: {{ $broker['website'] ?? 'https://forexpro.com' }}

If you have any questions or need assistance, our support team is available 24/7 at {{ $broker['support_email'] ?? 'support@forexpro.com' }}.

Happy trading!

Best regards,
The {{ $broker['name'] ?? 'ForexPro' }} Team

---
This email was sent to {{ $user['email'] ?? 'you' }} on {{ $user['signup_date'] ?? now()->format('F j, Y') }}.
© {{ date('Y') }} {{ $broker['name'] ?? 'ForexPro' }}. All rights reserved.
Unsubscribe: {{ url('/unsubscribe') }}
Privacy Policy: {{ url('/privacy') }} 