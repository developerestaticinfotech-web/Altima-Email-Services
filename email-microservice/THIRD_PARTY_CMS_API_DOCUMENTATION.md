# Third-Party CMS Integration APIs Documentation

## Overview
This document outlines the APIs created for third-party CMS integration with our RabbitMQ email processing system. These APIs allow external systems to send email payloads that will be queued and processed asynchronously.

## 📧 **Main Email Queue API**

### **Endpoint:** `POST /api/rabbitmq/send-email`
**Purpose:** Third-party CMS sends email payloads to be queued for processing

**Location:** `EmailController::sendEmailViaRabbitMQ()` (lines 1168-1245)

### **Request Payload:**
```json
{
    "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
    "provider_id": "01996243-2da2-72b2-80c2-9d3f855e404a",
    "user_id": "optional-user-id",
    "from": "sender@example.com",
    "to": ["recipient1@example.com", "recipient2@example.com"],
    "cc": ["cc@example.com"],
    "bcc": ["bcc@example.com"],
    "subject": "Email Subject",
    "body_format": "HTML",
    "body_content": "Email body content",
    "attachments": [],
    "scheduled_at": "2025-10-15T10:00:00Z"
}
```

### **Response (Success - 202 Accepted):**
```json
{
    "success": true,
    "message": "Email queued successfully for processing",
    "data": {
        "message_id": "unique-message-id",
        "outbox_id": "outbox-record-id",
        "status": "queued",
        "queued_at": "2025-10-15T12:00:00Z",
        "estimated_processing_time": "2-5 minutes"
    }
}
```

### **What it does:**
1. **Validates** the email payload
2. **Generates** unique message ID
3. **Publishes** to RabbitMQ queue (`email.send`)
4. **Creates outbox record** immediately with `pending` status
5. **Returns** queue confirmation with outbox ID

---

## 🔄 **Queue Processing API**

### **Endpoint:** `POST /api/rabbitmq/process-queue`
**Purpose:** Process queued emails from RabbitMQ

**Location:** `EmailController::processRabbitMQQueue()` (lines 1294-1328)

### **Request Parameters:**
```json
{
    "queue_name": "email.send",
    "max_messages": 10
}
```

### **Response:**
```json
{
    "success": true,
    "message": "Queue processing completed",
    "data": {
        "processed": 5,
        "success": 4,
        "failed": 1,
        "queue": "email.send"
    }
}
```

### **What it does:**
1. **Retrieves** messages from RabbitMQ queue
2. **Processes** each email using active email provider
3. **Updates** outbox records with results (`sent`/`failed`)
4. **Logs** comprehensive tracking data

---

## 📊 **Queue Status API**

### **Endpoint:** `GET /api/rabbitmq/queue-status`
**Purpose:** Check RabbitMQ queue status and connection

**Location:** `EmailController::getRabbitMQQueueStatus()` (lines 1250-1267)

### **Response:**
```json
{
    "success": true,
    "data": {
        "email_send_queue": {
            "name": "email.send",
            "message_count": 2,
            "status": "connected"
        },
        "email_sync_user_queue": {
            "name": "email.sync.user",
            "message_count": 0,
            "status": "connected"
        },
        "connection": {
            "host": "localhost",
            "port": 5672,
            "status": "connected"
        }
    }
}
```

---

## 📈 **Queue Statistics API**

### **Endpoint:** `GET /api/rabbitmq/queue-stats`
**Purpose:** Get detailed queue statistics and outbox counts

**Location:** `EmailController::getRabbitMQQueueStats()` (lines 1272-1289)

### **Response:**
```json
{
    "success": true,
    "data": {
        "queues": {
            "email.send": {
                "messages": 2,
                "consumers": 0,
                "status": "connected"
            },
            "email.sync.user": {
                "messages": 0,
                "consumers": 0,
                "status": "connected"
            }
        },
        "outbox_stats": {
            "total_pending": 1,
            "total_sent": 2,
            "total_failed": 0,
            "total": 3
        }
    }
}
```

---

## 🔄 **Complete Integration Flow**

### **Step 1: CMS Sends Email**
```bash
curl -X POST http://localhost:8000/api/rabbitmq/send-email \
  -H "Content-Type: application/json" \
  -d '{
    "tenant_id": "your-tenant-id",
    "provider_id": "your-provider-id",
    "from": "noreply@yourcms.com",
    "to": ["customer@example.com"],
    "subject": "Welcome Email",
    "body_format": "HTML",
    "body_content": "<h1>Welcome!</h1><p>Thank you for signing up.</p>"
  }'
```

### **Step 2: Email is Queued**
- ✅ **Immediately logged** to outbox table with `pending` status
- ✅ **Published** to RabbitMQ queue
- ✅ **Returns** confirmation with outbox ID

### **Step 3: Process Queue**
```bash
curl -X POST http://localhost:8000/api/rabbitmq/process-queue \
  -H "Content-Type: application/json" \
  -d '{
    "queue_name": "email.send",
    "max_messages": 10
  }'
```

### **Step 4: Email is Sent**
- ✅ **Retrieved** from RabbitMQ queue
- ✅ **Sent** using active email provider
- ✅ **Outbox updated** with `sent` status and tracking data

---

## 🎯 **Key Features for CMS Integration**

### **1. 📝 Immediate Logging**
- All emails are logged to the outbox table immediately upon queuing
- Provides instant confirmation and tracking

### **2. 🔄 Asynchronous Processing**
- Non-blocking queue system
- High-volume email processing capability
- Scalable architecture

### **3. 📊 Full Tracking**
- Complete audit trail in outbox table
- Processing time tracking
- Provider response logging
- Error handling and retry logic

### **4. 🛡️ Error Handling**
- Failed emails are logged with detailed error information
- Retry mechanism for failed emails
- Comprehensive error reporting

### **5. 🔍 Status Monitoring**
- Real-time queue status monitoring
- Processing statistics
- Outbox record tracking

### **6. 🔄 Retry Logic**
- Failed emails can be reprocessed
- Retry count tracking
- Automatic retry mechanisms

---

## 📋 **Outbox Table Structure**

The outbox table stores comprehensive information about each email:

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Unique identifier |
| `tenant_id` | UUID | Tenant identifier |
| `provider_id` | UUID | Email provider identifier |
| `user_id` | UUID | User identifier |
| `message_id` | String | Unique message ID |
| `subject` | String | Email subject |
| `from` | String | Sender email |
| `to` | JSON | Recipient emails array |
| `cc` | JSON | CC emails array |
| `bcc` | JSON | BCC emails array |
| `body_format` | String | HTML/Text format |
| `body_content` | Text | Email body content |
| `status` | String | pending/sent/failed/bounced |
| `sent_at` | DateTime | When email was sent |
| `delivered_at` | DateTime | When email was delivered |
| `error_message` | Text | Error details if failed |
| `retry_count` | Integer | Number of retry attempts |
| `processing_time_ms` | Integer | Processing time in milliseconds |

---

## 🚀 **Usage Examples**

### **PHP Example:**
```php
<?php
$emailData = [
    'tenant_id' => 'your-tenant-id',
    'provider_id' => 'your-provider-id',
    'from' => 'noreply@yourcms.com',
    'to' => ['customer@example.com'],
    'subject' => 'Welcome Email',
    'body_format' => 'HTML',
    'body_content' => '<h1>Welcome!</h1><p>Thank you for signing up.</p>'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/rabbitmq/send-email');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
echo "Email queued with ID: " . $result['data']['outbox_id'];
?>
```

### **JavaScript Example:**
```javascript
const emailData = {
    tenant_id: 'your-tenant-id',
    provider_id: 'your-provider-id',
    from: 'noreply@yourcms.com',
    to: ['customer@example.com'],
    subject: 'Welcome Email',
    body_format: 'HTML',
    body_content: '<h1>Welcome!</h1><p>Thank you for signing up.</p>'
};

fetch('http://localhost:8000/api/rabbitmq/send-email', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(emailData)
})
.then(response => response.json())
.then(data => {
    console.log('Email queued with ID:', data.data.outbox_id);
})
.catch(error => {
    console.error('Error:', error);
});
```

---

## 🔧 **Configuration Requirements**

### **Environment Variables:**
```env
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
```

### **Database Requirements:**
- MySQL database with outbox table
- Active email providers configured
- Tenant records set up

---

## 📞 **Support**

For technical support or questions about the API integration, please contact the development team.

**Last Updated:** October 15, 2025
**Version:** 1.0
**Status:** Production Ready
