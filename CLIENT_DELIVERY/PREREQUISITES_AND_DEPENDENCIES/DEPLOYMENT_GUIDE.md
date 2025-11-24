# Deployment Guide - AltimaCRM Email Microservice

## 🚀 Production Deployment Checklist

### Pre-Deployment Requirements
- [ ] Server provisioned with minimum requirements
- [ ] Domain name configured and DNS pointing to server
- [ ] SSL certificate obtained and configured
- [ ] Database credentials secured
- [ ] RabbitMQ credentials secured
- [ ] Email provider credentials configured
- [ ] Backup strategy implemented
- [ ] Monitoring tools configured

## 📋 Step-by-Step Deployment

### Step 1: Server Preparation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y curl wget git unzip software-properties-common

# Create application user
sudo adduser --system --group --home /var/www/email-microservice email-service
sudo usermod -a -G www-data email-service
```

### Step 2: Install Core Dependencies
```bash
# Install PHP 8.2
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-curl
sudo apt install -y php8.2-zip php8.2-mbstring php8.2-tokenizer php8.2-json
sudo apt install -y php8.2-bcmath php8.2-gd php8.2-imap php8.2-intl
sudo apt install -y php8.2-soap php8.2-xmlrpc php8.2-xsl php8.2-opcache
sudo apt install -y php8.2-redis php8.2-amqp

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install RabbitMQ
sudo apt install -y rabbitmq-server
sudo systemctl start rabbitmq-server
sudo systemctl enable rabbitmq-server

# Install Redis
sudo apt install -y redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Install Nginx
sudo apt install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Step 3: Application Deployment
```bash
# Clone repository
sudo git clone <repository-url> /var/www/email-microservice
sudo chown -R email-service:www-data /var/www/email-microservice
cd /var/www/email-microservice

# Install dependencies
sudo -u email-service composer install --no-dev --optimize-autoloader

# Set permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R email-service:www-data storage bootstrap/cache
```

### Step 4: Environment Configuration
```bash
# Create environment file
sudo -u email-service cp .env.example .env

# Generate application key
sudo -u email-service php artisan key:generate

# Configure environment variables
sudo nano .env
```

**Production .env Configuration:**
```env
APP_NAME="AltimaCRM Email Microservice"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=altimacrm_email
DB_USERNAME=email_user
DB_PASSWORD=secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=rabbitmq
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USERNAME=email_service
RABBITMQ_PASSWORD=secure_password
RABBITMQ_VHOST=email_vhost

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 5: Database Setup
```bash
# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE altimacrm_email CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'email_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON altimacrm_email.* TO 'email_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Run migrations
sudo -u email-service php artisan migrate --seed
```

### Step 6: RabbitMQ Configuration
```bash
# Enable management plugin
sudo rabbitmq-plugins enable rabbitmq_management

# Create user and set permissions
sudo rabbitmqctl add_user email_service secure_password
sudo rabbitmqctl set_user_tags email_service administrator
sudo rabbitmqctl set_permissions -p / email_service ".*" ".*" ".*"

# Create virtual host
sudo rabbitmqctl add_vhost email_vhost
sudo rabbitmqctl set_permissions -p email_vhost email_service ".*" ".*" ".*"
```

### Step 7: Nginx Configuration
```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/email-microservice
```

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/email-microservice/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Logging
    access_log /var/log/nginx/email-microservice.access.log;
    error_log /var/log/nginx/email-microservice.error.log;
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/email-microservice /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 8: SSL Certificate Setup
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### Step 9: Service Configuration
```bash
# Create systemd services
sudo nano /etc/systemd/system/email-queue-worker.service
```

```ini
[Unit]
Description=Email Queue Worker
After=network.target rabbitmq-server.service

[Service]
Type=simple
User=email-service
Group=www-data
WorkingDirectory=/var/www/email-microservice
ExecStart=/usr/bin/php artisan queue:work --queue=email.send --max-workers=3 --tries=3 --timeout=60
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
sudo nano /etc/systemd/system/email-inbound-fetcher.service
```

```ini
[Unit]
Description=Email Inbound Fetcher
After=network.target mysql.service

[Service]
Type=simple
User=email-service
Group=www-data
WorkingDirectory=/var/www/email-microservice
ExecStart=/usr/bin/php artisan email:fetch-inbound --interval=300
Restart=always
RestartSec=30
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start services
sudo systemctl daemon-reload
sudo systemctl enable email-queue-worker email-inbound-fetcher
sudo systemctl start email-queue-worker email-inbound-fetcher
```

### Step 10: Firewall Configuration
```bash
# Install UFW
sudo apt install -y ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Step 11: Monitoring Setup
```bash
# Create monitoring scripts
sudo nano /usr/local/bin/health-check.sh
```

```bash
#!/bin/bash
# Health check script

SERVICES=("nginx" "mysql" "rabbitmq-server" "redis-server" "email-queue-worker" "email-inbound-fetcher")
ALERT_EMAIL="admin@yourdomain.com"

for service in "${SERVICES[@]}"; do
    if ! systemctl is-active --quiet $service; then
        echo "$(date): Service $service is not running" >> /var/log/health-check.log
        systemctl restart $service
        sleep 10
        
        if ! systemctl is-active --quiet $service; then
            echo "Service $service failed to restart" | mail -s "Service Alert" $ALERT_EMAIL
        fi
    fi
done
```

```bash
sudo chmod +x /usr/local/bin/health-check.sh

# Add to crontab
sudo crontab -e
# Add: */5 * * * * /usr/local/bin/health-check.sh
```

### Step 12: Backup Configuration
```bash
# Create backup script
sudo nano /usr/local/bin/backup-email-service.sh
```

```bash
#!/bin/bash
# Backup script

BACKUP_DIR="/backup/email-microservice"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u email_user -p altimacrm_email > $BACKUP_DIR/database_$DATE.sql

# Backup application files
tar -czf $BACKUP_DIR/application_$DATE.tar.gz /var/www/email-microservice

# Backup configuration files
cp /var/www/email-microservice/.env $BACKUP_DIR/env_$DATE.backup
cp /etc/nginx/sites-available/email-microservice $BACKUP_DIR/nginx_$DATE.backup

# Clean up old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.backup" -mtime +30 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup-email-service.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-email-service.sh
```

## 🔍 Post-Deployment Verification

### 1. Service Status Check
```bash
# Check all services
sudo systemctl status nginx mysql rabbitmq-server redis-server email-queue-worker email-inbound-fetcher

# Check logs
sudo journalctl -u email-queue-worker -f
sudo journalctl -u email-inbound-fetcher -f
```

### 2. Application Health Check
```bash
# Test API endpoints
curl -X GET "https://your-domain.com/api/health"
curl -X GET "https://your-domain.com/api/rabbitmq/status"
curl -X GET "https://your-domain.com/api/email/stats"
```

### 3. Database Connection Test
```bash
# Test database connection
sudo -u email-service php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### 4. Queue Processing Test
```bash
# Test queue processing
sudo -u email-service php artisan tinker
>>> app(\App\Services\RabbitMQService::class)->testConnection();
>>> exit
```

### 5. Email Provider Test
```bash
# Test email sending
curl -X POST "https://your-domain.com/api/email/send" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "tenant_id": "your-tenant-id",
    "provider_id": "your-provider-id",
    "to": ["test@example.com"],
    "subject": "Test Email",
    "body_content": "This is a test email"
  }'
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Permission Issues
```bash
# Fix permissions
sudo chown -R email-service:www-data /var/www/email-microservice
sudo chmod -R 775 storage bootstrap/cache
```

#### 2. Service Not Starting
```bash
# Check service logs
sudo journalctl -u service-name -f

# Restart service
sudo systemctl restart service-name
```

#### 3. Database Connection Issues
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u email_user -p -h localhost altimacrm_email
```

#### 4. Queue Not Processing
```bash
# Check queue worker
sudo systemctl status email-queue-worker

# Check RabbitMQ
sudo rabbitmqctl list_queues
sudo rabbitmqctl list_connections
```

#### 5. SSL Certificate Issues
```bash
# Check certificate
sudo certbot certificates

# Renew certificate
sudo certbot renew
```

## 📊 Performance Optimization

### 1. PHP-FPM Optimization
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000
```

### 2. MySQL Optimization
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_log_buffer_size = 32M
query_cache_type = 1
query_cache_size = 128M
max_connections = 200
```

### 3. Redis Optimization
```bash
sudo nano /etc/redis/redis.conf
```

```ini
maxmemory 1gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

## 🔒 Security Hardening

### 1. File Permissions
```bash
# Secure sensitive files
sudo chmod 600 /var/www/email-microservice/.env
sudo chmod 600 /var/www/email-microservice/storage/app/private/*
```

### 2. Database Security
```sql
-- Remove test databases
DROP DATABASE IF EXISTS test;

-- Create read-only user
CREATE USER 'readonly'@'localhost' IDENTIFIED BY 'readonly_password';
GRANT SELECT ON altimacrm_email.* TO 'readonly'@'localhost';
```

### 3. Firewall Rules
```bash
# Restrict database access
sudo ufw deny 3306
sudo ufw deny 5672
sudo ufw deny 6379
```

## 📈 Monitoring and Alerting

### 1. Log Monitoring
```bash
# Monitor application logs
tail -f /var/www/email-microservice/storage/logs/laravel.log

# Monitor system logs
sudo journalctl -u email-queue-worker -f
sudo journalctl -u email-inbound-fetcher -f
```

### 2. Performance Monitoring
```bash
# Monitor system resources
htop
iotop
nethogs

# Monitor database
mysqladmin processlist
mysqladmin status
```

### 3. Queue Monitoring
```bash
# Monitor queue depth
watch -n 1 'rabbitmqctl list_queues name messages'

# Monitor connections
rabbitmqctl list_connections
```

## 🔄 Maintenance Procedures

### 1. Regular Updates
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Update application dependencies
cd /var/www/email-microservice
sudo -u email-service composer update
```

### 2. Database Maintenance
```bash
# Optimize tables
sudo -u email-service php artisan db:maintenance

# Clean up old records
sudo -u email-service php artisan log:cleanup --days=30
```

### 3. Log Rotation
```bash
# Configure log rotation
sudo nano /etc/logrotate.d/email-microservice
```

```
/var/www/email-microservice/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 email-service www-data
}
```

## 📞 Support and Maintenance

### Emergency Procedures
1. **Service Down:** Check logs and restart services
2. **Database Issues:** Check MySQL status and connections
3. **Queue Backlog:** Scale workers or check RabbitMQ
4. **High Memory Usage:** Restart services or optimize configuration
5. **SSL Issues:** Check certificate validity and renewal

### Contact Information
- **Technical Support:** support@yourdomain.com
- **Emergency Contact:** +1-XXX-XXX-XXXX
- **Documentation:** https://yourdomain.com/docs
- **Status Page:** https://status.yourdomain.com
