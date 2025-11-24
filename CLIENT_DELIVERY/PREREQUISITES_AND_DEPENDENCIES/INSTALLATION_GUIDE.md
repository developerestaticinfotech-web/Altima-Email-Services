# Installation Guide - AltimaCRM Email Microservice

## 📋 System Requirements

### Minimum Requirements
- **Operating System:** Ubuntu 20.04+ / CentOS 8+ / Windows 10+
- **PHP:** 8.1 or higher
- **MySQL:** 8.0 or higher
- **RabbitMQ:** 3.8 or higher
- **Memory:** 4GB RAM minimum
- **Storage:** 20GB free space minimum

### Recommended Requirements
- **Operating System:** Ubuntu 22.04 LTS
- **PHP:** 8.2 or higher
- **MySQL:** 8.0 or higher
- **RabbitMQ:** 3.11 or higher
- **Memory:** 8GB RAM or higher
- **Storage:** 50GB SSD

## 🔧 Software Dependencies

### 1. PHP Extensions Required
```bash
# Core extensions
php-cli
php-fpm
php-mysql
php-xml
php-curl
php-zip
php-mbstring
php-tokenizer
php-json
php-bcmath
php-gd
php-imap
php-intl
php-soap
php-xmlrpc
php-xsl
php-zip
php-opcache
php-redis
php-amqp
```

### 2. System Packages (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install -y nginx mysql-server rabbitmq-server redis-server
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml
sudo apt install -y php8.2-curl php8.2-zip php8.2-mbstring php8.2-tokenizer
sudo apt install -y php8.2-json php8.2-bcmath php8.2-gd php8.2-imap
sudo apt install -y php8.2-intl php8.2-soap php8.2-xmlrpc php8.2-xsl
sudo apt install -y php8.2-opcache php8.2-redis php8.2-amqp
sudo apt install -y composer git unzip
```

### 3. System Packages (CentOS/RHEL)
```bash
sudo yum update
sudo yum install -y nginx mysql-server rabbitmq-server redis
sudo yum install -y php82-php-fpm php82-php-cli php82-php-mysql php82-php-xml
sudo yum install -y php82-php-curl php82-php-zip php82-php-mbstring
sudo yum install -y php82-php-json php82-php-bcmath php82-php-gd php82-php-imap
sudo yum install -y php82-php-intl php82-php-soap php82-php-xmlrpc
sudo yum install -y php82-php-opcache php82-php-redis php82-php-amqp
sudo yum install -y composer git unzip
```

## 🚀 Installation Steps

### Step 1: Clone and Setup Project
```bash
# Clone the repository
git clone <repository-url> email-microservice
cd email-microservice

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Step 2: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Database Setup
```bash
# Create empty database (Laravel will handle table creation)
mysql -u root -p
CREATE DATABASE altimacrm_email CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'email_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON altimacrm_email.* TO 'email_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Laravel automatically creates all tables and populates initial data
php artisan migrate --seed
```

**What Laravel Does Automatically:**
- ✅ Creates all database tables using migrations
- ✅ Sets up proper indexes and foreign keys
- ✅ Populates initial data using seeders
- ✅ Creates default tenant and admin user
- ✅ Sets up sample email providers
- ✅ No manual SQL dumps needed!

### Step 4: RabbitMQ Configuration
```bash
# Start RabbitMQ service
sudo systemctl start rabbitmq-server
sudo systemctl enable rabbitmq-server



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

### Step 5: Redis Configuration
```bash
# Start Redis service
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Configure Redis (optional)
sudo nano /etc/redis/redis.conf
# Set: requirepass your_redis_password
sudo systemctl restart redis-server
```

### Step 6: Web Server Configuration

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/email-microservice/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

#### Apache Configuration (.htaccess)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Step 7: SSL Certificate (Production)
```bash
# Using Let's Encrypt
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com

# Or using self-signed certificate
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/email-microservice.key \
    -out /etc/ssl/certs/email-microservice.crt
```

## 🔧 Environment Configuration

### .env File Configuration
```enved
APP_NAME="AltimaCRM Email Microservice"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
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

## 🚀 Service Startup

### Start All Services
```bash
# Start web server
sudo systemctl start nginx
sudo systemctl enable nginx

# Start database
sudo systemctl start mysql
sudo systemctl enable mysql

# Start RabbitMQ
sudo systemctl start rabbitmq-server
sudo systemctl enable rabbitmq-server

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Start Laravel queue worker
php artisan queue:work --queue=email.send --max-workers=3 --tries=3 --timeout=60

# Start inbound email fetcher
php artisan email:fetch-inbound --interval=300
```

### Create Systemd Services

#### Email Queue Worker Service
```bash
sudo nano /etc/systemd/system/email-queue-worker.service
```

```ini
[Unit]
Description=Email Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/email-microservice
ExecStart=/usr/bin/php artisan queue:work --queue=email.send --max-workers=3 --tries=3 --timeout=60
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

#### Inbound Email Fetcher Service
```bash
sudo nano /etc/systemd/system/email-inbound-fetcher.service
```

```ini
[Unit]
Description=Email Inbound Fetcher
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/email-microservice
ExecStart=/usr/bin/php artisan email:fetch-inbound --interval=300
Restart=always
RestartSec=30

[Install]
WantedBy=multi-user.target
```

#### Enable Services
```bash
sudo systemctl daemon-reload
sudo systemctl enable email-queue-worker
sudo systemctl enable email-inbound-fetcher
sudo systemctl start email-queue-worker
sudo systemctl start email-inbound-fetcher
```

## 🔍 Verification

### Test Installation
```bash
# Check PHP version
php --version

# Check PHP extensions
php -m | grep -E "(mysql|imap|redis|amqp|gd|curl)"

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Test RabbitMQ connection
php artisan tinker
>>> app(\App\Services\RabbitMQService::class)->testConnection();

# Test Redis connection
php artisan tinker
>>> Redis::ping();
```

### Health Check Endpoints
```bash
# Check application health
curl http://your-domain.com/api/health

# Check RabbitMQ status
curl http://your-domain.com/api/rabbitmq/status

# Check database status
curl http://your-domain.com/api/email/stats
```

## 🛠️ Troubleshooting

### Common Issues

#### 1. PHP Extensions Missing
```bash
# Check installed extensions
php -m

# Install missing extensions
sudo apt install php8.2-imap php8.2-redis php8.2-amqp
```

#### 2. Database Connection Issues
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u email_user -p -h localhost altimacrm_email
```

#### 3. RabbitMQ Connection Issues
```bash
# Check RabbitMQ status
sudo systemctl status rabbitmq-server

# Check management interface
curl http://localhost:15672

# Check queues
sudo rabbitmqctl list_queues
```

#### 4. Permission Issues
```bash
# Fix Laravel permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 5. Queue Worker Not Processing
```bash
# Check queue worker status
sudo systemctl status email-queue-worker

# Check logs
sudo journalctl -u email-queue-worker -f

# Restart worker
sudo systemctl restart email-queue-worker
```

## 📊 Monitoring

### Log Files
- **Application Logs:** `storage/logs/laravel.log`
- **Queue Worker Logs:** `sudo journalctl -u email-queue-worker`
- **Inbound Fetcher Logs:** `sudo journalctl -u email-inbound-fetcher`
- **Nginx Logs:** `/var/log/nginx/error.log`
- **MySQL Logs:** `/var/log/mysql/error.log`
- **RabbitMQ Logs:** `/var/log/rabbitmq/rabbit@hostname.log`

### Performance Monitoring
```bash
# Monitor queue depth
watch -n 1 'rabbitmqctl list_queues name messages'

# Monitor system resources
htop

# Monitor database connections
mysql -e "SHOW PROCESSLIST;"
```

## 🔒 Security Hardening

### 1. Firewall Configuration
```bash
# Allow only necessary ports
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### 2. Database Security
```sql
-- Remove test databases
DROP DATABASE IF EXISTS test;

-- Create read-only user for monitoring
CREATE USER 'monitor'@'localhost' IDENTIFIED BY 'monitor_password';
GRANT SELECT ON altimacrm_email.* TO 'monitor'@'localhost';
```

### 3. File Permissions
```bash
# Secure sensitive files
chmod 600 .env
chmod 600 storage/app/private/*
```

## 📈 Scaling Considerations

### Horizontal Scaling
1. **Load Balancer:** Use Nginx or HAProxy
2. **Multiple Workers:** Deploy multiple queue workers
3. **Database Replication:** Set up MySQL master-slave
4. **Redis Cluster:** For high availability caching

### Vertical Scaling
1. **Increase RAM:** For larger queues and caching
2. **SSD Storage:** For better database performance
3. **CPU Cores:** For more concurrent workers

## 🔄 Backup Strategy

### Database Backup
```bash
# Daily backup script
#!/bin/bash
mysqldump -u email_user -p altimacrm_email > /backup/email_db_$(date +%Y%m%d).sql
```

### Application Backup
```bash
# Backup application files
tar -czf /backup/email-microservice_$(date +%Y%m%d).tar.gz /path/to/email-microservice
```

### Configuration Backup
```bash
# Backup configuration files
cp .env /backup/env_$(date +%Y%m%d).backup
cp /etc/nginx/sites-available/email-microservice /backup/nginx_$(date +%Y%m%d).backup
```
