# RabbitMQ Integration Guide

## 🐰 Overview
The email microservice uses RabbitMQ for asynchronous email processing. This document explains how to integrate with the RabbitMQ queues for both outbound and inbound email processing.

## 📋 Queue Configuration

### Queue Names
- **Outbound Emails:** `email.send`
- **Inbound Emails:** `email.inbound`
- **User Sync:** `email.sync.user`

### Exchange
- **Type:** Direct Exchange
- **Name:** `email.exchange`
- **Durable:** Yes

## 📤 Outbound Email Queue Integration

### Queue: `email.send`

**Purpose:** Process outbound emails asynchronously

**Message Format:**
```json
{
    "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
    "provider_id": "0198a819-e5d3-703a-a39a-1b77e3ece687",
    "user_id": "user-123",
    "message_id": "unique-message-id",
    "from": "noreply@company.com",
    "to": ["user@example.com"],
    "cc": ["cc@example.com"],
    "bcc": ["bcc@example.com"],
    "subject": "Test Email",
    "body_format": "HTML",
    "body_content": "<p>Hello World</p>",
    "attachments": [
        {
            "filename": "document.pdf",
            "url": "https://example.com/files/document.pdf",
            "mime_type": "application/pdf"
        }
    ],
    "headers": {
        "X-Custom-Header": "Custom Value",
        "X-Priority": "1"
    },
    "template_id": "template-123",
    "campaign_id": "campaign-456",
    "timestamp": "2025-09-19T10:30:00Z"
}
```

**Processing Flow:**
1. CRM publishes message to `email.send` queue
2. Email microservice worker picks up message
3. Worker loads provider configuration from database
4. Worker sends email via configured provider (SMTP/API)
5. Worker updates `outbox` table with status
6. Worker publishes result to monitoring system

**Expected Response:**
```json
{
    "success": true,
    "message_id": "unique-message-id",
    "status": "sent",
    "provider_response": {
        "provider_name": "Gmail",
        "provider_message_id": "gmail-12345",
        "sent_at": "2025-09-19T10:30:05Z"
    }
}
```

## 📥 Inbound Email Queue Integration

### Queue: `email.inbound`

**Purpose:** Process inbound emails for CRM consumption

**Message Format:**
```json
{
    "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
    "provider_id": "0198a819-e5d3-703a-a39a-1b77e3ece687",
    "message_id": "inbound-message-id",
    "in_reply_to": "original-message-id",
    "references": "ref1,ref2",
    "subject": "Re: Test Email",
    "from_email": "user@example.com",
    "from_name": "John Doe",
    "to_emails": ["noreply@company.com"],
    "cc_emails": ["cc@example.com"],
    "bcc_emails": [],
    "body_format": "HTML",
    "body_content": "<p>Thank you for your email...</p>",
    "attachments": [
        {
            "filename": "response.pdf",
            "url": "https://example.com/files/response.pdf",
            "mime_type": "application/pdf"
        }
    ],
    "headers": {
        "Message-ID": "inbound-message-id",
        "In-Reply-To": "original-message-id",
        "References": "ref1,ref2"
    },
    "is_reply": true,
    "is_forward": false,
    "is_auto_reply": false,
    "thread_id": "thread-123",
    "received_at": "2025-09-19T11:00:00Z",
    "priority": "normal",
    "source": "imap"
}
```

**Processing Flow:**
1. Email microservice fetches emails from IMAP/POP3
2. Service processes and parses email content
3. Service publishes to `email.inbound` queue
4. CRM consumes messages from queue
5. CRM processes inbound emails in their system

## 🔄 User Sync Queue Integration

### Queue: `email.sync.user`

**Purpose:** Trigger manual email sync for specific users

**Message Format:**
```json
{
    "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
    "provider_id": "0198a819-e5d3-703a-a39a-1b77e3ece687",
    "user_id": "user-123",
    "sync_type": "inbound",
    "timestamp": "2025-09-19T10:30:00Z"
}
```

## 🔧 RabbitMQ Connection Configuration

### Environment Variables
```env
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USERNAME=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
RABBITMQ_EMAIL_SEND_QUEUE=email.send
RABBITMQ_EMAIL_INBOUND_QUEUE=email.inbound
RABBITMQ_EMAIL_SYNC_USER_QUEUE=email.sync.user
```

### Connection String
```
amqp://username:password@host:port/vhost
```

## 📊 Queue Monitoring

### Queue Statistics
```bash
# Check queue status
curl -X GET "http://localhost:8000/api/rabbitmq/status"

# Response
{
    "success": true,
    "connection_status": "connected",
    "queues": {
        "email.send": {
            "message_count": 5,
            "consumer_count": 2,
            "status": "running"
        },
        "email.inbound": {
            "message_count": 3,
            "consumer_count": 1,
            "status": "running"
        }
    }
}
```

### Health Checks
- **Queue Health:** Monitor message count and consumer count
- **Connection Health:** Verify RabbitMQ connection status
- **Worker Health:** Ensure workers are running and processing messages

## 🚀 CRM Integration Examples

### PHP Example (Laravel)
```php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EmailQueueService
{
    private $connection;
    private $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', 'localhost'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USERNAME', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest')
        );
        $this->channel = $this->connection->channel();
    }

    public function sendEmail($emailData)
    {
        $this->channel->queue_declare('email.send', false, true, false, false);
        
        $message = new AMQPMessage(
            json_encode($emailData),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
        
        $this->channel->basic_publish($message, '', 'email.send');
    }

    public function consumeInboundEmails($callback)
    {
        $this->channel->queue_declare('email.inbound', false, true, false, false);
        
        $this->channel->basic_consume(
            'email.inbound',
            '',
            false,
            false,
            false,
            false,
            $callback
        );
        
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }
}
```

### Node.js Example
```javascript
const amqp = require('amqplib');

class EmailQueueService {
    constructor() {
        this.connection = null;
        this.channel = null;
    }

    async connect() {
        this.connection = await amqp.connect('amqp://localhost');
        this.channel = await this.connection.createChannel();
    }

    async sendEmail(emailData) {
        await this.channel.assertQueue('email.send', { durable: true });
        
        this.channel.sendToQueue(
            'email.send',
            Buffer.from(JSON.stringify(emailData)),
            { persistent: true }
        );
    }

    async consumeInboundEmails(callback) {
        await this.channel.assertQueue('email.inbound', { durable: true });
        
        this.channel.consume('email.inbound', (msg) => {
            if (msg) {
                const emailData = JSON.parse(msg.content.toString());
                callback(emailData);
                this.channel.ack(msg);
            }
        });
    }
}
```

### Python Example
```python
import pika
import json

class EmailQueueService:
    def __init__(self):
        self.connection = pika.BlockingConnection(
            pika.ConnectionParameters('localhost')
        )
        self.channel = self.connection.channel()

    def send_email(self, email_data):
        self.channel.queue_declare(queue='email.send', durable=True)
        
        self.channel.basic_publish(
            exchange='',
            routing_key='email.send',
            body=json.dumps(email_data),
            properties=pika.BasicProperties(
                delivery_mode=2,  # Make message persistent
            )
        )

    def consume_inbound_emails(self, callback):
        self.channel.queue_declare(queue='email.inbound', durable=True)
        
        def on_message(ch, method, properties, body):
            email_data = json.loads(body)
            callback(email_data)
            ch.basic_ack(delivery_tag=method.delivery_tag)
        
        self.channel.basic_consume(
            queue='email.inbound',
            on_message_callback=on_message
        )
        
        self.channel.start_consuming()
```

## ⚠️ Error Handling

### Dead Letter Queue
Failed messages are automatically moved to dead letter queues:
- `email.send.dlq` - Failed outbound emails
- `email.inbound.dlq` - Failed inbound emails

### Retry Logic
- **Max Retries:** 3 attempts
- **Retry Delay:** Exponential backoff (1s, 5s, 15s)
- **Dead Letter:** After max retries exceeded

### Error Response Format
```json
{
    "success": false,
    "error": "Error message",
    "retry_count": 2,
    "max_retries": 3,
    "next_retry_at": "2025-09-19T10:35:00Z"
}
```

## 🔒 Security Considerations

1. **Authentication:** Use strong RabbitMQ credentials
2. **SSL/TLS:** Enable SSL for production environments
3. **VHost Isolation:** Use separate virtual hosts for different environments
4. **Message Encryption:** Consider encrypting sensitive data in messages
5. **Access Control:** Implement proper user permissions

## 📈 Performance Optimization

1. **Connection Pooling:** Reuse connections when possible
2. **Batch Processing:** Process multiple messages in batches
3. **Worker Scaling:** Scale workers based on queue depth
4. **Message Compression:** Compress large messages
5. **Queue Monitoring:** Monitor queue depth and processing rates

## 🛠️ Troubleshooting

### Common Issues
1. **Connection Refused:** Check RabbitMQ service status
2. **Queue Not Found:** Ensure queue is declared before use
3. **Message Not Delivered:** Check exchange and routing key
4. **High Memory Usage:** Monitor queue depth and consumer count
5. **Slow Processing:** Check worker performance and database queries

### Debug Commands
```bash
# Check RabbitMQ status
sudo systemctl status rabbitmq-server

# List queues
rabbitmqctl list_queues

# Check connections
rabbitmqctl list_connections

# Monitor queue depth
watch -n 1 'rabbitmqctl list_queues name messages'
```
