# AltimaCRM Email Microservice

A Laravel-based microservice responsible for sending emails with AWS SES integration, designed with privacy and security in mind.

## 🚀 Features

- **Email Sending**: Send emails using dynamic templates with business data
- **Template Management**: Store and manage email templates in the database
- **AWS SES Integration**: Secure email delivery via Amazon SES
- **Privacy Focused**: Masks internal infrastructure using sending subdomains
- **Webhook Support**: Handle delivery confirmations, bounces, and complaints
- **Comprehensive Logging**: Track all email activities and delivery status
- **API-First Design**: RESTful API endpoints for integration
- **Queue Support**: Asynchronous email processing with database queues

## 🏗️ Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Client App    │───▶│  Email Service   │───▶│   AWS SES      │
│   (CRM, API)    │    │   (Laravel)      │    │   (Provider)    │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌──────────────────┐
                       │   Webhooks      │
                       │  (SES Events)   │
                       └──────────────────┘
```

## 📋 Requirements

- PHP 8.1+
- Laravel 10+
- MySQL 8.0+
- AWS SES Account
- Composer

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd email-microservice
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Set application key**
   ```bash
   php artisan key:generate
   ```

5. **Configure database**
   ```bash
   # Update .env with your MySQL credentials
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=altimacrm_email
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Configure AWS SES**
   ```bash
   # Update .env with your AWS credentials
   AWS_ACCESS_KEY_ID=your_access_key
   AWS_SECRET_ACCESS_KEY=your_secret_key
   AWS_DEFAULT_REGION=us-east-1
   ```

7. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

8. **Start the server**
   ```bash
   php artisan serve
   ```

## 🔧 Configuration

### Environment Variables

```env
# Email Service Settings
EMAIL_SERVICE_NAME=altimacrm-email
EMAIL_SERVICE_VERSION=1.0.0
EMAIL_SERVICE_ENVIRONMENT=development

# Privacy & Security Settings
EMAIL_SENDING_DOMAIN=mailer.broker.com
EMAIL_BOUNCE_DOMAIN=bounce.mailer.broker.com
EMAIL_UNSUBSCRIBE_DOMAIN=unsubscribe.mailer.broker.com

# Rate Limiting
EMAIL_RATE_LIMIT_PER_MINUTE=60
EMAIL_RATE_LIMIT_PER_HOUR=1000

# Webhook Settings
EMAIL_WEBHOOK_SECRET=your_webhook_secret
EMAIL_WEBHOOK_URL=http://localhost:8000/api/email/webhook
```

### DNS Configuration

For the sending subdomain `mailer.broker.com`:

```dns
# SPF Record
mailer.broker.com. IN TXT "v=spf1 include:amazonses.com -all"

# DKIM Record (provided by AWS SES)
selector._domainkey.mailer.broker.com. IN TXT "your_dkim_key"

# DMARC Record
_dmarc.broker.com. IN TXT "v=DMARC1; p=none; sp=none; adkim=r; aspf=r; pct=100; rua=mailto:dmarc@broker.com"
```

## 📧 API Endpoints

### Email Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/email/send` | Send an email using a template |
| `GET` | `/api/email/status/{messageId}` | Get email delivery status |
| `GET` | `/api/email/templates` | Get available email templates |
| `GET` | `/api/email/templates/{templateId}` | Get specific template |
| `GET` | `/api/email/logs` | Get email logs with filters |
| `GET` | `/api/email/stats` | Get email statistics |

### Webhook Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/webhook/ses` | AWS SES webhook endpoint |
| `POST` | `/api/webhook/{provider}` | Generic webhook endpoint |
| `GET` | `/api/webhook/stats` | Get webhook statistics |
| `GET` | `/api/webhook/events` | Get webhook events |

### System

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/health` | Service health check |
| `GET` | `/api/` | API documentation |

## 📝 Email Template Structure

### Template Variables

```php
[
    'user' => [
        'name' => 'User full name',
        'email' => 'User email address',
        'signup_date' => 'User signup date',
        'referral_code' => 'User referral code',
    ],
    'account' => [
        'type' => 'Account type',
        'leverage' => 'Account leverage',
        'currency' => 'Account currency',
        'balance' => 'Account balance',
    ],
    'broker' => [
        'name' => 'Broker name',
        'support_email' => 'Support email',
        'website' => 'Broker website',
    ],
]
```

### Sending Email

```php
// Example API request
POST /api/email/send
{
    "template_id": "welcome_user",
    "to": [
        {
            "email": "john.doe@example.com",
            "name": "John Doe"
        }
    ],
    "data": {
        "user": {
            "name": "John Doe",
            "email": "john.doe@example.com",
            "signup_date": "2025-08-11",
            "referral_code": "FX123JOHN"
        },
        "account": {
            "type": "Pro",
            "leverage": "1:500",
            "currency": "USD",
            "balance": 0
        },
        "broker": {
            "name": "ForexPro",
            "support_email": "support@forexpro.com",
            "website": "https://forexpro.com"
        }
    },
    "source": "crm"
}
```

## 🔒 Security Features

- **Privacy Headers**: Masks internal infrastructure
- **Custom MAIL FROM**: Uses sending subdomain for bounces
- **SPF/DKIM/DMARC**: Proper email authentication
- **Rate Limiting**: Prevents abuse
- **Input Validation**: Comprehensive request validation
- **Error Logging**: Secure error handling without exposing sensitive data

## 📊 Monitoring & Logging

### Email Logs

All email activities are logged with:
- Message ID and status
- Template used
- Recipient information
- Delivery timestamps
- Provider responses
- Error messages

### Webhook Events

Track delivery events:
- Bounces (permanent/transient)
- Complaints
- Deliveries
- Opens and clicks

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Configure production database
- [ ] Set up AWS SES production environment
- [ ] Configure DNS records
- [ ] Set up SSL certificates
- [ ] Configure webhook endpoints
- [ ] Set up monitoring and alerting
- [ ] Test email delivery
- [ ] Verify privacy headers

### Docker Support

```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

## 🧪 Testing

### Run Tests

```bash
php artisan test
```

### Test Email Sending

```bash
# Test with sample data
curl -X POST http://localhost:8000/api/email/send \
  -H "Content-Type: application/json" \
  -d '{
    "template_id": "welcome_user",
    "to": [{"email": "test@example.com", "name": "Test User"}],
    "data": {
      "user": {"name": "Test User", "email": "test@example.com"},
      "broker": {"name": "TestBroker"}
    }
  }'
```

## 📚 Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [AWS SES Documentation](https://docs.aws.amazon.com/ses/)
- [Email Privacy Best Practices](https://tools.ietf.org/html/rfc8058)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Contact the development team
- Check the documentation

## 🔄 Changelog

### Version 1.0.0
- Initial release
- Basic email sending functionality
- AWS SES integration
- Template management
- Webhook support
- Privacy-focused design
