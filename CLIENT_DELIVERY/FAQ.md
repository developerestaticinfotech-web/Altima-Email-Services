# Frequently Asked Questions (FAQ)

## 🗄️ Database Questions

### Q: Do I need to create a database dump or SQL file?
**A: No!** Laravel handles database setup automatically through migrations and seeders. You only need to:

1. Create an empty database
2. Run `php artisan migrate --seed`
3. Laravel automatically creates all tables and populates initial data

### Q: What data is included when I run `php artisan migrate --seed`?
**A: The seeders will create:**
- Default tenant (AltimaCRM)
- Sample email providers (Gmail, AWS SES, Postmark)
- Admin user (admin@altimacrm.com / admin)
- Demo users for testing
- All necessary database tables with proper relationships

### Q: Can I customize the initial data?
**A: Yes!** You can modify the seeder files in `database/seeders/` to customize:
- Tenant information
- Email provider configurations
- Default users and passwords
- Sample data

## 🚀 Installation Questions

### Q: What's the minimum server requirements?
**A: Minimum:**
- 4GB RAM, 20GB storage, 2 CPU cores
- PHP 8.1+, MySQL 8.0+, RabbitMQ 3.8+, Redis 6.0+

**Recommended:**
- 8GB RAM, 50GB SSD, 4 CPU cores
- PHP 8.2+, MySQL 8.0+, RabbitMQ 3.11+, Redis 6.0+

### Q: Can I install this on Windows?
**A: Yes!** The application works on:
- Windows 10+ with XAMPP/WAMP
- Linux (Ubuntu/CentOS recommended)
- macOS
- Docker containers

### Q: Do I need to install all PHP extensions manually?
**A: Most package managers install them automatically, but you may need:**
```bash
# Ubuntu/Debian
sudo apt install php8.2-imap php8.2-redis php8.2-amqp

# CentOS/RHEL
sudo yum install php82-php-imap php82-php-redis php82-php-amqp
```

## 🔧 Configuration Questions

### Q: How do I configure email providers?
**A: Two ways:**
1. **Via Web UI:** Go to `/providers` page and add providers
2. **Via Database:** Update `email_providers` table directly

### Q: How do I set up IMAP for inbound emails?
**A: When adding an email provider:**
1. Select protocol (IMAP/POP3)
2. Enter IMAP settings (host, port, username, password)
3. Save configuration
4. The system will automatically fetch emails using these settings

### Q: Can I use multiple email providers per tenant?
**A: Yes!** Each tenant can have multiple email providers configured.

## 🐰 RabbitMQ Questions

### Q: Do I need to configure RabbitMQ queues manually?
**A: No!** The application automatically:
- Creates required queues
- Sets up exchanges
- Configures routing
- Handles message persistence

### Q: How do I monitor queue performance?
**A: Use the built-in monitoring:**
- Web UI: `http://localhost:15672`
- API endpoint: `GET /api/rabbitmq/status`
- Command line: `rabbitmqctl list_queues`

### Q: What happens if RabbitMQ is down?
**A: The system includes:**
- Automatic reconnection
- Message persistence
- Dead letter queues
- Fallback API endpoints

## 📧 Email Processing Questions

### Q: How often are inbound emails fetched?
**A: By default every 5 minutes, but you can configure:**
- CRON job frequency
- Manual refresh via UI
- Real-time webhook processing

### Q: Can I process emails in real-time?
**A: Yes!** You can:
- Set up webhooks for instant processing
- Use shorter CRON intervals
- Implement push notifications

### Q: What email formats are supported?
**A: The system supports:**
- HTML emails
- Plain text emails
- Multipart emails
- Emails with attachments
- Emails with inline images

## 🔒 Security Questions

### Q: How secure is the email data?
**A: The system includes:**
- Encrypted database connections
- Secure file storage
- SSL/TLS encryption
- Input validation and sanitization
- Role-based access control

### Q: Can I restrict access to specific tenants?
**A: Yes!** The system supports:
- Multi-tenant isolation
- User-based authentication
- Role-based permissions
- API rate limiting

### Q: How do I backup email data?
**A: Multiple backup options:**
- Database backups (MySQL dump)
- File system backups
- Cloud storage integration
- Automated backup scripts

## 🚨 Troubleshooting Questions

### Q: Why aren't emails being sent?
**A: Check:**
1. Queue worker is running: `sudo systemctl status email-queue-worker`
2. RabbitMQ is running: `sudo systemctl status rabbitmq-server`
3. Email provider configuration is correct
4. Check logs: `tail -f storage/logs/laravel.log`

### Q: Why aren't inbound emails being fetched?
**A: Check:**
1. Inbound fetcher is running: `sudo systemctl status email-inbound-fetcher`
2. IMAP/POP3 settings are correct
3. Provider credentials are valid
4. Check logs: `sudo journalctl -u email-inbound-fetcher -f`

### Q: How do I restart all services?
**A: Use these commands:**
```bash
# Restart core services
sudo systemctl restart nginx mysql rabbitmq-server redis-server

# Restart email services
sudo systemctl restart email-queue-worker email-inbound-fetcher
```

## 📊 Performance Questions

### Q: How many emails can the system handle?
**A: Depends on server specs:**
- **Basic setup:** 1000+ emails/hour
- **Optimized setup:** 10,000+ emails/hour
- **Enterprise setup:** 100,000+ emails/hour

### Q: How do I scale the system?
**A: Multiple scaling options:**
- Add more queue workers
- Use load balancers
- Implement database replication
- Use Redis clustering

### Q: How do I monitor performance?
**A: Built-in monitoring:**
- API endpoints for statistics
- Queue depth monitoring
- Database performance metrics
- System resource monitoring

## 🔄 Maintenance Questions

### Q: How often should I update the system?
**A: Recommended schedule:**
- **Security updates:** Immediately
- **Minor updates:** Monthly
- **Major updates:** Quarterly
- **Dependencies:** As needed

### Q: How do I update the application?
**A: Update process:**
```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer update

# Run migrations (if any)
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
```

### Q: How do I backup the system?
**A: Backup strategy:**
- **Database:** Daily automated backups
- **Files:** Weekly file system backups
- **Configuration:** Version control
- **Logs:** Monthly log rotation

## 📞 Support Questions

### Q: Where can I get help?
**A: Multiple support channels:**
- Documentation in this package
- API reference guides
- Troubleshooting guides
- Technical support contact

### Q: How do I report bugs?
**A: Include:**
- Error messages and logs
- Steps to reproduce
- System configuration
- Expected vs actual behavior

### Q: Can I customize the system?
**A: Yes!** The system is designed for:
- Custom email providers
- Custom authentication
- Custom API endpoints
- Custom UI modifications

---

**Still have questions?** Check the detailed documentation in the respective folders or contact technical support.
