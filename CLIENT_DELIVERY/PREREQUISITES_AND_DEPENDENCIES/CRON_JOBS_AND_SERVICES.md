# CRON Jobs and Services Configuration

## 📋 Overview
This document outlines all the required CRON jobs and background services for the AltimaCRM Email Microservice to function properly.

## 🔄 Required CRON Jobs

### 1. Email Queue Worker (Primary Service)
**Purpose:** Process outbound emails from RabbitMQ queue

**Command:**
```bash
php artisan queue:work --queue=email.send --max-workers=3 --tries=3 --timeout=60
```

**CRON Entry:**
```bash
# Run continuously (recommended for production)
@reboot cd /path/to/email-microservice && php artisan queue:work --queue=email.send --max-workers=3 --tries=3 --timeout=60 > /var/log/email-queue-worker.log 2>&1
```

**Systemd Service (Recommended):**
```ini
[Unit]
Description=Email Queue Worker
After=network.target rabbitmq-server.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/path/to/email-microservice
ExecStart=/usr/bin/php artisan queue:work --queue=email.send --max-workers=3 --tries=3 --timeout=60
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

### 2. Inbound Email Fetcher
**Purpose:** Fetch emails from IMAP/POP3 providers and save to database

**Command:**
```bash
php artisan email:fetch-inbound --interval=300
```

**CRON Entry:**
```bash
# Run every 5 minutes
*/5 * * * * cd /path/to/email-microservice && php artisan email:fetch-inbound --interval=300 > /var/log/email-inbound-fetcher.log 2>&1
```

**Systemd Service (Recommended):**
```ini
[Unit]
Description=Email Inbound Fetcher
After=network.target mysql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/path/to/email-microservice
ExecStart=/usr/bin/php artisan email:fetch-inbound --interval=300
Restart=always
RestartSec=30
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

### 3. Queue Monitor and Health Check
**Purpose:** Monitor queue health and restart workers if needed

**Command:**
```bash
php artisan queue:monitor --max-failures=10 --max-time=3600
```

**CRON Entry:**
```bash
# Run every 10 minutes
*/10 * * * * cd /path/to/email-microservice && php artisan queue:monitor --max-failures=10 --max-time=3600 > /var/log/queue-monitor.log 2>&1
```

### 4. Log Rotation and Cleanup
**Purpose:** Clean up old logs and temporary files

**Command:**
```bash
php artisan log:cleanup --days=30
```

**CRON Entry:**
```bash
# Run daily at 2 AM
0 2 * * * cd /path/to/email-microservice && php artisan log:cleanup --days=30 > /var/log/log-cleanup.log 2>&1
```

### 5. Database Maintenance
**Purpose:** Optimize database tables and clean up old records

**Command:**
```bash
php artisan db:maintenance --cleanup-days=90
```

**CRON Entry:**
```bash
# Run weekly on Sunday at 3 AM
0 3 * * 0 cd /path/to/email-microservice && php artisan db:maintenance --cleanup-days=90 > /var/log/db-maintenance.log 2>&1
```

### 6. Failed Job Retry
**Purpose:** Retry failed jobs after a delay

**Command:**
```bash
php artisan queue:retry --failed-jobs --delay=300
```

**CRON Entry:**
```bash
# Run every 15 minutes
*/15 * * * * cd /path/to/email-microservice && php artisan queue:retry --failed-jobs --delay=300 > /var/log/queue-retry.log 2>&1
```

## 🚀 Background Services

### 1. RabbitMQ Service
**Purpose:** Message queue broker for email processing

**Service Configuration:**
```bash
# Start RabbitMQ
sudo systemctl start rabbitmq-server
sudo systemctl enable rabbitmq-server

# Check status
sudo systemctl status rabbitmq-server
```

**Configuration File:** `/etc/rabbitmq/rabbitmq.conf`
```ini
# Basic configuration
listeners.tcp.default = 5672
management.tcp.port = 15672
management.tcp.ip = 0.0.0.0

# Memory and disk limits
vm_memory_high_watermark.relative = 0.6
disk_free_limit.absolute = 1GB

# Logging
log.console = true
log.console.level = info
log.file = /var/log/rabbitmq/rabbit.log
log.file.level = info

# Security
default_user = guest
default_pass = guest
```

### 2. MySQL Service
**Purpose:** Database for storing email data and configurations

**Service Configuration:**
```bash
# Start MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Check status
sudo systemctl status mysql
```

**Configuration File:** `/etc/mysql/mysql.conf.d/mysqld.cnf`
```ini
[mysqld]
# Basic settings
port = 3306
bind-address = 127.0.0.1
max_connections = 200

# Memory settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_log_buffer_size = 16M

# Query cache
query_cache_type = 1
query_cache_size = 64M

# Logging
log_error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### 3. Redis Service
**Purpose:** Caching and session storage

**Service Configuration:**
```bash
# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Check status
sudo systemctl status redis-server
```

**Configuration File:** `/etc/redis/redis.conf`
```ini
# Basic settings
port 6379
bind 127.0.0.1
timeout 0
tcp-keepalive 300

# Memory management
maxmemory 512mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log
```

### 4. Nginx Service
**Purpose:** Web server for API endpoints

**Service Configuration:**
```bash
# Start Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check status
sudo systemctl status nginx
```

## 📊 Monitoring Services

### 1. Queue Depth Monitor
**Purpose:** Monitor queue depth and alert if queues are backing up

**Script:** `/usr/local/bin/queue-monitor.sh`
```bash
#!/bin/bash

# Configuration
QUEUE_THRESHOLD=100
ALERT_EMAIL="admin@yourdomain.com"
LOG_FILE="/var/log/queue-monitor.log"

# Check queue depth
QUEUE_DEPTH=$(rabbitmqctl list_queues name messages | grep "email.send" | awk '{print $2}')

if [ "$QUEUE_DEPTH" -gt "$QUEUE_THRESHOLD" ]; then
    echo "$(date): Queue depth $QUEUE_DEPTH exceeds threshold $QUEUE_THRESHOLD" >> $LOG_FILE
    echo "Queue depth alert: $QUEUE_DEPTH messages in email.send queue" | mail -s "Queue Alert" $ALERT_EMAIL
fi
```

**CRON Entry:**
```bash
# Run every 5 minutes
*/5 * * * * /usr/local/bin/queue-monitor.sh
```

### 2. Service Health Check
**Purpose:** Check all services are running and restart if needed

**Script:** `/usr/local/bin/health-check.sh`
```bash
#!/bin/bash

# Services to check
SERVICES=("nginx" "mysql" "rabbitmq-server" "redis-server" "email-queue-worker" "email-inbound-fetcher")

for service in "${SERVICES[@]}"; do
    if ! systemctl is-active --quiet $service; then
        echo "$(date): Service $service is not running, attempting restart" >> /var/log/health-check.log
        systemctl restart $service
        sleep 10
        
        if ! systemctl is-active --quiet $service; then
            echo "$(date): Failed to restart $service" >> /var/log/health-check.log
            echo "Service $service failed to restart" | mail -s "Service Alert" admin@yourdomain.com
        fi
    fi
done
```

**CRON Entry:**
```bash
# Run every 2 minutes
*/2 * * * * /usr/local/bin/health-check.sh
```

### 3. Disk Space Monitor
**Purpose:** Monitor disk space and clean up if needed

**Script:** `/usr/local/bin/disk-monitor.sh`
```bash
#!/bin/bash

# Configuration
DISK_THRESHOLD=85
LOG_DIR="/var/log"
TEMP_DIR="/tmp"

# Check disk usage
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')

if [ "$DISK_USAGE" -gt "$DISK_THRESHOLD" ]; then
    echo "$(date): Disk usage $DISK_USAGE% exceeds threshold $DISK_THRESHOLD%" >> /var/log/disk-monitor.log
    
    # Clean up old logs
    find $LOG_DIR -name "*.log" -mtime +7 -delete
    find $TEMP_DIR -name "tmp*" -mtime +1 -delete
    
    # Clean up Laravel logs
    find /path/to/email-microservice/storage/logs -name "*.log" -mtime +30 -delete
fi
```

**CRON Entry:**
```bash
# Run every hour
0 * * * * /usr/local/bin/disk-monitor.sh
```

## 🔧 Service Management Commands

### Start All Services
```bash
# Start core services
sudo systemctl start nginx mysql rabbitmq-server redis-server

# Start email services
sudo systemctl start email-queue-worker email-inbound-fetcher
```

### Stop All Services
```bash
# Stop email services first
sudo systemctl stop email-queue-worker email-inbound-fetcher

# Stop core services
sudo systemctl stop nginx mysql rabbitmq-server redis-server
```

### Restart All Services
```bash
# Restart core services
sudo systemctl restart nginx mysql rabbitmq-server redis-server

# Restart email services
sudo systemctl restart email-queue-worker email-inbound-fetcher
```

### Check Service Status
```bash
# Check all services
sudo systemctl status nginx mysql rabbitmq-server redis-server email-queue-worker email-inbound-fetcher

# Check specific service
sudo systemctl status email-queue-worker
```

## 📈 Performance Tuning

### Queue Worker Optimization
```bash
# Increase worker count for high volume
php artisan queue:work --queue=email.send --max-workers=5 --tries=3 --timeout=60

# Use multiple queues for different priorities
php artisan queue:work --queue=high,normal,low --max-workers=3
```

### Database Optimization
```sql
-- Optimize tables regularly
OPTIMIZE TABLE outbox, inbound_emails, email_providers, tenants;

-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'altimacrm_email'
ORDER BY (data_length + index_length) DESC;
```

### Memory Optimization
```bash
# Increase PHP memory limit
echo "memory_limit = 512M" >> /etc/php/8.2/fpm/php.ini

# Increase MySQL buffer pool
echo "innodb_buffer_pool_size = 2G" >> /etc/mysql/mysql.conf.d/mysqld.cnf

# Restart services
sudo systemctl restart php8.2-fpm mysql
```

## 🚨 Alerting Configuration

### Email Alerts
```bash
# Install mailutils
sudo apt install mailutils

# Configure SMTP
sudo nano /etc/postfix/main.cf
```

### Slack Integration
```bash
# Create Slack webhook script
cat > /usr/local/bin/slack-alert.sh << 'EOF'
#!/bin/bash
WEBHOOK_URL="https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK"
MESSAGE="$1"
curl -X POST -H 'Content-type: application/json' \
    --data "{\"text\":\"$MESSAGE\"}" \
    $WEBHOOK_URL
EOF

chmod +x /usr/local/bin/slack-alert.sh
```

## 📋 Complete CRON Setup

### Crontab Configuration
```bash
# Edit crontab
sudo crontab -e

# Add all CRON jobs
# Email Queue Worker (continuous)
@reboot cd /path/to/email-microservice && php artisan queue:work --queue=email.send --max-workers=3 --tries=3 --timeout=60 > /var/log/email-queue-worker.log 2>&1

# Inbound Email Fetcher (every 5 minutes)
*/5 * * * * cd /path/to/email-microservice && php artisan email:fetch-inbound --interval=300 > /var/log/email-inbound-fetcher.log 2>&1

# Queue Monitor (every 10 minutes)
*/10 * * * * cd /path/to/email-microservice && php artisan queue:monitor --max-failures=10 --max-time=3600 > /var/log/queue-monitor.log 2>&1

# Log Cleanup (daily at 2 AM)
0 2 * * * cd /path/to/email-microservice && php artisan log:cleanup --days=30 > /var/log/log-cleanup.log 2>&1

# Database Maintenance (weekly on Sunday at 3 AM)
0 3 * * 0 cd /path/to/email-microservice && php artisan db:maintenance --cleanup-days=90 > /var/log/db-maintenance.log 2>&1

# Failed Job Retry (every 15 minutes)
*/15 * * * * cd /path/to/email-microservice && php artisan queue:retry --failed-jobs --delay=300 > /var/log/queue-retry.log 2>&1

# Queue Depth Monitor (every 5 minutes)
*/5 * * * * /usr/local/bin/queue-monitor.sh

# Health Check (every 2 minutes)
*/2 * * * * /usr/local/bin/health-check.sh

# Disk Space Monitor (every hour)
0 * * * * /usr/local/bin/disk-monitor.sh
```

## 🔍 Troubleshooting

### Common Issues

#### 1. Queue Worker Not Processing
```bash
# Check worker status
sudo systemctl status email-queue-worker

# Check logs
sudo journalctl -u email-queue-worker -f

# Restart worker
sudo systemctl restart email-queue-worker
```

#### 2. Inbound Fetcher Not Working
```bash
# Check IMAP connection
php artisan tinker
>>> app(\App\Services\EmailFetcherService::class)->testConnection();

# Check provider configuration
php artisan tinker
>>> App\Models\EmailProvider::where('is_active', true)->get();
```

#### 3. High Memory Usage
```bash
# Check memory usage
free -h
ps aux --sort=-%mem | head

# Restart services
sudo systemctl restart email-queue-worker email-inbound-fetcher
```

#### 4. Database Connection Issues
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u email_user -p -h localhost altimacrm_email

# Check connections
mysql -e "SHOW PROCESSLIST;"
```

### Log Analysis
```bash
# Check application logs
tail -f /path/to/email-microservice/storage/logs/laravel.log

# Check system logs
sudo journalctl -u email-queue-worker -f
sudo journalctl -u email-inbound-fetcher -f

# Check error logs
tail -f /var/log/nginx/error.log
tail -f /var/log/mysql/error.log
```
