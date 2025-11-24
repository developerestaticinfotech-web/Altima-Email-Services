# API Reference - AltimaCRM Email Microservice

## Base URL
```
http://your-domain.com/api/email
```

## Authentication
All API endpoints require authentication. Include the following header:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

## 📧 Email Management APIs

### 1. Send Email
**Endpoint:** `POST /api/email/send`

**Description:** Send an email through the microservice using RabbitMQ queue.

**Request Body:**
```json
{
    "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
    "provider_id": "0198a819-e5d3-703a-a39a-1b77e3ece687",
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
            "url": "https://example.com/files/document.pdf"
        }
    ],
    "header_overrides": {
        "X-Custom-Header": "Custom Value"
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Email queued successfully",
    "data": {
        "message_id": "unique-message-id",
        "status": "queued",
        "outbox_id": "12345"
    }
}
```

### 2. Get Email Status
**Endpoint:** `GET /api/email/status/{messageId}`

**Description:** Check the status of a sent email.

**Response:**
```json
{
    "success": true,
    "data": {
        "message_id": "unique-message-id",
        "status": "sent",
        "sent_at": "2025-09-19T10:30:00Z",
        "delivered_at": "2025-09-19T10:30:05Z",
        "provider_response": {
            "provider_name": "Gmail",
            "provider_message_id": "gmail-12345"
        }
    }
}
```

### 3. Get Email Logs
**Endpoint:** `GET /api/email/logs`

**Description:** Retrieve email logs with filtering options.

**Query Parameters:**
- `tenant_id` (optional): Filter by tenant
- `status` (optional): Filter by status (pending, sent, failed, bounced)
- `date_from` (optional): Start date (YYYY-MM-DD)
- `date_to` (optional): End date (YYYY-MM-DD)
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "12345",
            "message_id": "unique-message-id",
            "subject": "Test Email",
            "from": "noreply@company.com",
            "to": ["user@example.com"],
            "status": "sent",
            "sent_at": "2025-09-19T10:30:00Z",
            "provider_name": "Gmail"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 100,
        "last_page": 5
    }
}
```

## 📥 Inbound Email APIs

### 4. Get Inbound Emails
**Endpoint:** `GET /api/email/inbound`

**Description:** Retrieve inbound emails with filtering options.

**Query Parameters:**
- `tenant_id` (required): Tenant ID
- `status` (optional): Filter by status (new, processed, queued, delivered, failed)
- `is_reply` (optional): Filter replies (true/false)
- `from_email` (optional): Filter by sender email
- `date_from` (optional): Start date (YYYY-MM-DD)
- `date_to` (optional): End date (YYYY-MM-DD)
- `page` (optional): Page number
- `per_page` (optional): Items per page

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "inbound-123",
            "message_id": "inbound-message-id",
            "subject": "Re: Test Email",
            "from_email": "user@example.com",
            "from_name": "John Doe",
            "to_emails": ["noreply@company.com"],
            "body_content": "Thank you for your email...",
            "is_reply": true,
            "received_at": "2025-09-19T11:00:00Z",
            "status": "processed"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 50,
        "last_page": 3
    }
}
```

### 5. Create Inbound Email (Manual/Webhook)
**Endpoint:** `POST /api/email/inbound`

**Description:** Manually create an inbound email record (for webhooks or manual entry).

**Request Body:**
```json
{
    "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
    "provider_id": "0198a819-e5d3-703a-a39a-1b77e3ece687",
    "message_id": "inbound-message-id",
    "subject": "Re: Test Email",
    "from_email": "user@example.com",
    "from_name": "John Doe",
    "to_emails": ["noreply@company.com"],
    "body_format": "HTML",
    "body_content": "<p>Thank you for your email...</p>",
    "is_reply": true,
    "in_reply_to": "original-message-id"
}
```

### 6. Get Inbound Email Statistics
**Endpoint:** `GET /api/email/inbound/stats`

**Description:** Get statistics for inbound emails.

**Query Parameters:**
- `tenant_id` (optional): Filter by tenant

**Response:**
```json
{
    "success": true,
    "data": {
        "total_emails": 150,
        "new_emails": 25,
        "processed_emails": 120,
        "replies": 45,
        "recent_activity": [
            {
                "date": "2025-09-19",
                "count": 12
            }
        ]
    }
}
```

## 🐰 RabbitMQ Integration APIs

### 7. Get RabbitMQ Status
**Endpoint:** `GET /api/rabbitmq/status`

**Description:** Check RabbitMQ connection and queue status.

**Response:**
```json
{
    "success": true,
    "connection_status": "connected",
    "queues": {
        "email.send": {
            "message_count": 5,
            "consumer_count": 2
        },
        "email.inbound": {
            "message_count": 3,
            "consumer_count": 1
        }
    }
}
```

### 8. Publish to Email Queue
**Endpoint:** `POST /api/rabbitmq/publish`

**Description:** Manually publish a message to RabbitMQ email queue.

**Request Body:**
```json
{
    "queue": "email.send",
    "message": {
        "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
        "provider_id": "0198a819-e5d3-703a-a39a-1b77e3ece687",
        "from": "noreply@company.com",
        "to": ["user@example.com"],
        "subject": "Test Email",
        "body_content": "Hello World"
    }
}
```

## 🏢 Tenant Management APIs

### 9. Get Tenants
**Endpoint:** `GET /api/email/tenants`

**Description:** Get list of all tenants.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
            "tenant_name": "AltimaCRM",
            "status": "active"
        }
    ]
}
```

### 10. Get Email Statistics
**Endpoint:** `GET /api/email/stats`

**Description:** Get overall email statistics.

**Query Parameters:**
- `tenant_id` (optional): Filter by tenant

**Response:**
```json
{
    "success": true,
    "data": {
        "total_sent": 1250,
        "total_delivered": 1200,
        "total_failed": 25,
        "total_bounced": 25,
        "total_inbound": 300,
        "providers": {
            "Gmail": 800,
            "AWS SES": 300,
            "Postmark": 150
        }
    }
}
```

## 🔄 Bounced Email Management

### 11. Get Bounced Emails
**Endpoint:** `GET /api/email/bounced`

**Description:** Get list of bounced emails.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "12345",
            "message_id": "bounced-message-id",
            "to": "invalid@example.com",
            "bounce_reason": "Mailbox does not exist",
            "bounced_at": "2025-09-19T10:30:00Z"
        }
    ]
}
```

### 12. Requeue Bounced Email
**Endpoint:** `POST /api/email/bounced/{id}/requeue`

**Description:** Requeue a bounced email for retry.

**Response:**
```json
{
    "success": true,
    "message": "Email requeued successfully",
    "data": {
        "outbox_id": "12345",
        "new_status": "queued"
    }
}
```

## 🔐 Authentication APIs

### 13. Login
**Endpoint:** `POST /api/auth/login`

**Request Body:**
```json
{
    "email": "admin@altimacrm.com",
    "password": "admin"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": "12345",
            "name": "Admin User",
            "email": "admin@altimacrm.com",
            "tenant": {
                "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
                "tenant_name": "AltimaCRM"
            }
        },
        "token": "your-access-token"
    }
}
```

### 14. Get Current User
**Endpoint:** `GET /api/auth/me`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": "12345",
        "name": "Admin User",
        "email": "admin@altimacrm.com",
        "tenant": {
            "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
            "tenant_name": "AltimaCRM"
        }
    }
}
```

## 📊 Error Responses

All endpoints may return the following error responses:

### 400 Bad Request
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": ["The field is required."]
    }
}
```

### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthorized access"
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Internal server error",
    "error": "Detailed error message"
}
```

## 🔧 Rate Limiting

- **Standard endpoints:** 100 requests per minute per IP
- **Email sending:** 50 requests per minute per tenant
- **RabbitMQ operations:** 200 requests per minute per IP

## 📝 Notes

1. All timestamps are in ISO 8601 format (UTC)
2. All UUIDs are in standard format
3. Pagination starts from page 1
4. Maximum file size for attachments: 25MB
5. Supported email formats: HTML, Text, JSON
6. Maximum recipients per email: 100 (to), 50 (cc), 50 (bcc)
