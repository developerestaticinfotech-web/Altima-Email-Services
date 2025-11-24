# Software Dependencies - AltimaCRM Email Microservice

## 📋 Core Dependencies

### 1. PHP 8.1+ (Required)
**Version:** 8.1 or higher (8.2+ recommended)  
**Purpose:** Core runtime for Laravel application

**Installation:**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.2-fpm php8.2-cli

# CentOS/RHEL
sudo yum install php82-php-fpm php82-php-cli
```

**Required Extensions:**
- `php-cli` - Command line interface
- `php-fpm` - FastCGI Process Manager
- `php-mysql` - MySQL database driver
- `php-xml` - XML processing
- `php-curl` - HTTP client
- `php-zip` - Archive handling
- `php-mbstring` - Multibyte string handling
- `php-tokenizer` - Token parsing
- `php-json` - JSON processing
- `php-bcmath` - Arbitrary precision mathematics
- `php-gd` - Image processing
- `php-imap` - IMAP/POP3 email access
- `php-intl` - Internationalization
- `php-soap` - SOAP web services
- `php-xmlrpc` - XML-RPC support
- `php-xsl` - XSL transformations
- `php-opcache` - Opcode caching
- `php-redis` - Redis driver
- `php-amqp` - RabbitMQ driver

### 2. MySQL 8.0+ (Required)
**Version:** 8.0 or higher  
**Purpose:** Primary database for email data storage

**Installation:**
```bash
# Ubuntu/Debian
sudo apt install mysql-server

# CentOS/RHEL
sudo yum install mysql-server
```

**Configuration Requirements:**
- Character Set: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`
- InnoDB Engine (default)
- Minimum 1GB RAM allocation
- Binary logging enabled

### 3. RabbitMQ 3.8+ (Required)
**Version:** 3.8 or higher (3.11+ recommended)  
**Purpose:** Message queue for asynchronous email processing

**Installation:**
```bash
# Ubuntu/Debian
sudo apt install rabbitmq-server

# CentOS/RHEL
sudo yum install rabbitmq-server
```

**Required Plugins:**
- `rabbitmq_management` - Web management interface
- `rabbitmq_delayed_message_exchange` - Delayed message support
- `rabbitmq_shovel` - Message shovel plugin

### 4. Redis 6.0+ (Required)
**Version:** 6.0 or higher  
**Purpose:** Caching and session storage

**Installation:**
```bash
# Ubuntu/Debian
sudo apt install redis-server

# CentOS/RHEL
sudo yum install redis
```

**Configuration Requirements:**
- Persistence enabled (RDB + AOF)
- Memory limit configured
- Password authentication (production)

## 🌐 Web Server Dependencies

### 1. Nginx 1.18+ (Recommended)
**Version:** 1.18 or higher  
**Purpose:** Web server and reverse proxy

**Installation:**
```bash
# Ubuntu/Debian
sudo apt install nginx

# CentOS/RHEL
sudo yum install nginx
```

**Required Modules:**
- `http_ssl_module` - SSL/TLS support
- `http_gzip_module` - Compression
- `http_rewrite_module` - URL rewriting
- `http_proxy_module` - Reverse proxy

### 2. Apache 2.4+ (Alternative)
**Version:** 2.4 or higher  
**Purpose:** Alternative web server

**Installation:**
```bash
# Ubuntu/Debian
sudo apt install apache2

# CentOS/RHEL
sudo yum install httpd
```

**Required Modules:**
- `mod_rewrite` - URL rewriting
- `mod_ssl` - SSL/TLS support
- `mod_headers` - HTTP headers
- `mod_deflate` - Compression

## 🛠️ Development Dependencies

### 1. Composer 2.0+ (Required)
**Version:** 2.0 or higher  
**Purpose:** PHP dependency manager

**Installation:**
```bash
# Download and install
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 2. Git 2.0+ (Required)
**Version:** 2.0 or higher  
**Purpose:** Version control

**Installation:**
```bash
# Ubuntu/Debian
sudo apt install git

# CentOS/RHEL
sudo yum install git
```

### 3. Node.js 16+ (Optional)
**Version:** 16 or higher  
**Purpose:** Frontend asset compilation

**Installation:**
```bash
# Using NodeSource repository
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs
```

## 📦 PHP Packages (Composer Dependencies)

### Core Laravel Packages
```json
{
    "laravel/framework": "^10.0",
    "laravel/tinker": "^2.8",
    "laravel/sanctum": "^3.2"
}
```

### Database Packages
```json
{
    "doctrine/dbal": "^3.6",
    "spatie/laravel-permission": "^5.10"
}
```

### Queue and Caching Packages
```json
{
    "php-amqplib/php-amqplib": "^3.2",
    "predis/predis": "^2.0",
    "laravel/horizon": "^5.15"
}
```

### Email and Communication Packages
```json
{
    "phpoffice/phpspreadsheet": "^1.29",
    "swiftmailer/swiftmailer": "^6.3",
    "guzzlehttp/guzzle": "^7.7"
}
```

### Utility Packages
```json
{
    "ramsey/uuid": "^4.7",
    "carbon/carbon": "^2.66",
    "monolog/monolog": "^3.4",
    "vlucas/phpdotenv": "^5.5"
}
```

## 🔧 System Dependencies

### 1. SSL/TLS Certificates
**Purpose:** Secure HTTPS communication

**Options:**
- Let's Encrypt (free, automated)
- Commercial certificates
- Self-signed certificates (development only)

**Installation (Let's Encrypt):**
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### 2. Firewall Configuration
**Purpose:** Network security

**Required Ports:**
- `22` - SSH access
- `80` - HTTP traffic
- `443` - HTTPS traffic
- `3306` - MySQL (restrict to localhost)
- `5672` - RabbitMQ (restrict to localhost)
- `6379` - Redis (restrict to localhost)
- `15672` - RabbitMQ Management (restrict to localhost)

**Configuration (UFW):**
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 3. Log Rotation
**Purpose:** Manage log file sizes

**Installation:**
```bash
sudo apt install logrotate
```

**Configuration:** `/etc/logrotate.d/email-microservice`
```
/var/log/email-*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

## 📊 Monitoring Dependencies

### 1. System Monitoring
**Tools:** htop, iotop, netstat, ss

**Installation:**
```bash
sudo apt install htop iotop net-tools
```

### 2. Database Monitoring
**Tools:** mysqladmin, mysqltuner, pt-query-digest

**Installation:**
```bash
# MySQL tools
sudo apt install mysql-client

# Percona Toolkit (optional)
wget https://repo.percona.com/apt/percona-release_latest.$(lsb_release -sc)_all.deb
sudo dpkg -i percona-release_latest.$(lsb_release -sc)_all.deb
sudo apt update
sudo apt install percona-toolkit
```

### 3. Queue Monitoring
**Tools:** rabbitmqctl, rabbitmqadmin

**Installation:**
```bash
# RabbitMQ management tools
sudo apt install rabbitmq-server
sudo rabbitmq-plugins enable rabbitmq_management
```

## 🔒 Security Dependencies

### 1. Fail2Ban
**Purpose:** Intrusion prevention

**Installation:**
```bash
sudo apt install fail2ban
```

**Configuration:** `/etc/fail2ban/jail.local`
```ini
[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log
maxretry = 3
```

### 2. UFW (Uncomplicated Firewall)
**Purpose:** Firewall management

**Installation:**
```bash
sudo apt install ufw
```

### 3. SSL/TLS Tools
**Tools:** openssl, certbot

**Installation:**
```bash
sudo apt install openssl certbot
```

## 📈 Performance Dependencies

### 1. OPcache
**Purpose:** PHP opcode caching

**Configuration:** `/etc/php/8.2/fpm/conf.d/10-opcache.ini`
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Redis Configuration
**Purpose:** Optimized caching

**Configuration:** `/etc/redis/redis.conf`
```ini
maxmemory 512mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### 3. MySQL Optimization
**Purpose:** Database performance

**Configuration:** `/etc/mysql/mysql.conf.d/mysqld.cnf`
```ini
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_log_buffer_size = 16M
query_cache_type = 1
query_cache_size = 64M
```

## 🐳 Container Dependencies (Optional)

### 1. Docker
**Purpose:** Containerization

**Installation:**
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
```

### 2. Docker Compose
**Purpose:** Multi-container orchestration

**Installation:**
```bash
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

## 📋 Dependency Verification Script

### Check All Dependencies
```bash
#!/bin/bash
# dependency-check.sh

echo "=== Checking System Dependencies ==="

# Check PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    echo "✅ PHP $PHP_VERSION installed"
else
    echo "❌ PHP not installed"
fi

# Check MySQL
if command -v mysql &> /dev/null; then
    MYSQL_VERSION=$(mysql --version | cut -d' ' -f3 | cut -d',' -f1)
    echo "✅ MySQL $MYSQL_VERSION installed"
else
    echo "❌ MySQL not installed"
fi

# Check RabbitMQ
if command -v rabbitmqctl &> /dev/null; then
    RABBITMQ_VERSION=$(rabbitmqctl version | head -n1 | cut -d' ' -f3)
    echo "✅ RabbitMQ $RABBITMQ_VERSION installed"
else
    echo "❌ RabbitMQ not installed"
fi

# Check Redis
if command -v redis-cli &> /dev/null; then
    REDIS_VERSION=$(redis-cli --version | cut -d' ' -f2 | cut -d'=' -f2)
    echo "✅ Redis $REDIS_VERSION installed"
else
    echo "❌ Redis not installed"
fi

# Check Composer
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version | cut -d' ' -f3)
    echo "✅ Composer $COMPOSER_VERSION installed"
else
    echo "❌ Composer not installed"
fi

# Check PHP Extensions
echo -e "\n=== Checking PHP Extensions ==="
REQUIRED_EXTENSIONS=("mysql" "imap" "redis" "amqp" "gd" "curl" "zip" "mbstring" "xml" "json" "bcmath")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        echo "✅ $ext extension installed"
    else
        echo "❌ $ext extension missing"
    fi
done

# Check Services
echo -e "\n=== Checking Services ==="
SERVICES=("nginx" "mysql" "rabbitmq-server" "redis-server")

for service in "${SERVICES[@]}"; do
    if systemctl is-active --quiet $service; then
        echo "✅ $service is running"
    else
        echo "❌ $service is not running"
    fi
done

echo -e "\n=== Dependency Check Complete ==="
```

## 🔄 Update and Maintenance

### Regular Updates
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Update PHP packages
composer update

# Update Node.js packages (if using)
npm update
```

### Security Updates
```bash
# Check for security updates
sudo apt list --upgradable | grep -i security

# Apply security updates
sudo apt upgrade -y
```

### Backup Dependencies
```bash
# Backup Composer packages
composer show --installed > composer-packages.txt

# Backup system packages
dpkg --get-selections > installed-packages.txt
```

## 📞 Support and Troubleshooting

### Common Issues

#### 1. PHP Extension Missing
```bash
# Install missing extension
sudo apt install php8.2-extension-name

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### 2. Service Not Starting
```bash
# Check service status
sudo systemctl status service-name

# Check logs
sudo journalctl -u service-name -f

# Restart service
sudo systemctl restart service-name
```

#### 3. Permission Issues
```bash
# Fix Laravel permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 4. Memory Issues
```bash
# Check memory usage
free -h
htop

# Increase PHP memory limit
echo "memory_limit = 512M" >> /etc/php/8.2/fpm/php.ini
sudo systemctl restart php8.2-fpm
```

### Getting Help
- **Laravel Documentation:** https://laravel.com/docs
- **RabbitMQ Documentation:** https://www.rabbitmq.com/documentation.html
- **MySQL Documentation:** https://dev.mysql.com/doc/
- **Redis Documentation:** https://redis.io/documentation
