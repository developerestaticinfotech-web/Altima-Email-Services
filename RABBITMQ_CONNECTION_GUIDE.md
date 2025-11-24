# 🐰 RabbitMQ Connection Guide

## ✅ Current Status
- **Processed:** 2 emails
- **Success:** 0
- **Failed:** 2

The queue processing is working, but emails are failing. Let's fix the connection and check why emails failed.

---

## 📍 Where to Check Queue Emails

### **1. View Failed Emails in Outbox**
**URL:** `http://localhost:8000/outbox`

**Steps:**
1. Go to `http://localhost:8000/outbox`
2. Select your tenant from the dropdown
3. In the **Status filter**, select **"failed"**
4. Click **"Apply Filters"**
5. You'll see all failed emails with error messages

### **2. View All Queue Emails**
**URL:** `http://localhost:8000/outbox`

**Steps:**
1. Go to `http://localhost:8000/outbox`
2. Select your tenant
3. Leave status filter as "All" to see all emails (pending, sent, failed)
4. Check the status column to see email states

### **3. Check Laravel Logs**
**Location:** `email-microservice/storage/logs/laravel.log`

**To view errors:**
```bash
cd email-microservice
tail -f storage/logs/laravel.log
```

Or open the file in your editor to see detailed error messages.

---

## 🔧 How to Connect RabbitMQ with This Project

### **Step 1: Configure RabbitMQ in .env File**

Open `email-microservice/.env` and add/update these variables:

```env
# RabbitMQ Configuration
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
```

**Default Values (if RabbitMQ is installed locally):**
- **Host:** `localhost` (or `127.0.0.1`)
- **Port:** `5672` (default RabbitMQ port)
- **User:** `guest` (default user)
- **Password:** `guest` (default password)
- **VHost:** `/` (default virtual host)

### **Step 2: Verify RabbitMQ is Running**

**On Windows:**
```powershell
# Check if RabbitMQ service is running
Get-Service RabbitMQ

# Or check in Services (services.msc)
# Look for "RabbitMQ" service
```

**Start RabbitMQ (if not running):**
```powershell
# Start RabbitMQ service
Start-Service RabbitMQ
```

**Or use RabbitMQ Management:**
- Open browser: `http://localhost:15672`
- Default login: `guest` / `guest`
- If you can access this, RabbitMQ is running

### **Step 3: Test RabbitMQ Connection**

**Option 1: Use the Test Page**
1. Go to `http://localhost:8000/rabbitmq-test`
2. Click **"Refresh Status"** button
3. Check **"Connection Status"** - should show connected

**Option 2: Test via API**
```bash
# Check queue status
curl http://localhost:8000/api/rabbitmq/queue-status
```

**Option 3: Check Laravel Logs**
After trying to process queue, check logs:
```bash
cd email-microservice
tail -f storage/logs/laravel.log | grep -i rabbitmq
```

### **Step 4: Verify Configuration File**

The configuration is in: `email-microservice/config/rabbitmq.php`

It reads from `.env` file:
```php
'host' => env('RABBITMQ_HOST', 'localhost'),
'port' => env('RABBITMQ_PORT', 5672),
'user' => env('RABBITMQ_USER', 'guest'),
'password' => env('RABBITMQ_PASSWORD', 'guest'),
'vhost' => env('RABBITMQ_VHOST', '/'),
```

### **Step 5: Clear Laravel Cache (if needed)**

After updating `.env`:
```bash
cd email-microservice
php artisan config:clear
php artisan cache:clear
```

---

## 🔍 Why Are Emails Failing?

Common reasons for email failures:

### **1. Missing Email Provider Configuration**
- Check: `http://localhost:8000/providers`
- Ensure you have an active email provider configured
- Provider must have valid SMTP credentials

### **2. Invalid Template**
- Template ID in queue message doesn't exist
- Template is inactive
- Template data is missing required variables

### **3. SMTP Connection Issues**
- Email provider SMTP settings are incorrect
- Firewall blocking SMTP port
- Provider credentials expired

### **4. Missing Tenant/Provider**
- `tenant_id` or `provider_id` in queue message is invalid
- Provider not found in database

---

## 📊 Check Failed Email Details

### **Method 1: Via Outbox Page**
1. Go to `http://localhost:8000/outbox`
2. Filter by status: **"failed"**
3. Click **"View"** button on any failed email
4. Check the **error_message** field for details

### **Method 2: Via API**
```bash
# Get failed emails
curl "http://localhost:8000/api/outbox/emails?tenant_id=YOUR_TENANT_ID&status=failed"
```

### **Method 3: Check Database**
```sql
SELECT id, subject, from, to, status, error_message, created_at 
FROM outbox 
WHERE status = 'failed' 
ORDER BY created_at DESC;
```

---

## 🛠️ Troubleshooting Steps

### **1. Verify RabbitMQ Connection**
```bash
# Test connection
cd email-microservice
php artisan tinker
```

Then in tinker:
```php
$service = app(\App\Services\RabbitMQService::class);
$status = $service->getQueueStatus();
print_r($status);
```

### **2. Check Queue Messages**
Go to RabbitMQ Management UI:
- URL: `http://localhost:15672`
- Login: `guest` / `guest`
- Navigate to **Queues** tab
- Check `email.send` queue
- See messages waiting to be processed

### **3. Check Email Provider**
1. Go to `http://localhost:8000/providers`
2. Verify you have an active provider
3. Check provider SMTP settings are correct
4. Test provider connection

### **4. Check Laravel Logs**
```bash
cd email-microservice
# View recent errors
tail -n 100 storage/logs/laravel.log | grep -i error
```

---

## ✅ Quick Checklist

- [ ] RabbitMQ service is running
- [ ] `.env` file has RabbitMQ configuration
- [ ] Can access `http://localhost:15672` (RabbitMQ Management)
- [ ] Connection status shows "Connected" on test page
- [ ] Email provider is configured and active
- [ ] Templates exist in database
- [ ] Check failed emails in outbox for error details

---

## 🎯 Next Steps

1. **Check Failed Emails:**
   - Go to `http://localhost:8000/outbox`
   - Filter by "failed" status
   - View error messages

2. **Verify RabbitMQ Connection:**
   - Update `.env` with RabbitMQ settings
   - Test connection at `http://localhost:8000/rabbitmq-test`

3. **Fix Email Provider:**
   - Ensure provider is configured correctly
   - Check SMTP credentials

4. **Re-process Queue:**
   - After fixing issues, click "Process Queue" again
   - Monitor success/failure count

---

## 📝 Configuration Example

**Complete `.env` RabbitMQ section:**
```env
# RabbitMQ Configuration
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/

# Optional: Advanced settings
RABBITMQ_HEARTBEAT=60
RABBITMQ_READ_WRITE_TIMEOUT=60
RABBITMQ_CONNECTION_TIMEOUT=10
```

---

## 🔗 Useful URLs

- **Outbox (View Emails):** `http://localhost:8000/outbox`
- **RabbitMQ Test:** `http://localhost:8000/rabbitmq-test`
- **RabbitMQ Management:** `http://localhost:15672`
- **Queue Status API:** `http://localhost:8000/api/rabbitmq/queue-status`
- **Process Queue API:** `POST http://localhost:8000/api/rabbitmq/process-queue`

---

**Need Help?** Check the error messages in:
1. Outbox page (failed emails)
2. Laravel logs (`storage/logs/laravel.log`)
3. RabbitMQ Management UI (`http://localhost:15672`)

