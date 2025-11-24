# 📧 AltimaCRM Email Microservice - Complete Project Understanding

## 🎯 Project Overview

**AltimaCRM Email Microservice** is a comprehensive, production-ready Laravel-based email management system designed for multi-tenant CRM applications. It provides complete email sending, receiving, processing, and tracking capabilities with support for multiple email providers, RabbitMQ integration, and full audit logging.

---

## 🏗️ Architecture & Design

### **System Architecture**

```
┌─────────────────────────────────────────────────────────────────┐
│                    EXTERNAL SYSTEMS                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   CRM App    │  │  RabbitMQ    │  │ Email Providers│        │
│  │              │  │   Server     │  │ (AWS SES,     │        │
│  │              │  │              │  │  Gmail, etc)  │        │
│  └──────┬───────┘  └──────┬───────┘  └──────┬────────┘        │
└─────────┼─────────────────┼──────────────────┼─────────────────┘
          │                 │                  │
          │ HTTP/REST       │ AMQP             │ SMTP/API
          │                 │                  │
┌─────────▼─────────────────▼──────────────────▼─────────────────┐
│              EMAIL MICROSERVICE (Laravel)                       │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  API Layer (Controllers)                                  │ │
│  │  - EmailController    - WebhookController                 │ │
│  │  - InboundEmailController - OutboxController               │ │
│  └──────────────────────────────────────────────────────────┘ │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  Service Layer                                           │ │
│  │  - EmailService          - RabbitMQService                │ │
│  │  - EmailProcessingService - EmailFetcherService           │ │
│  │  - WebhookService        - MimeParserService              │ │
│  │  - FileStorageService                                     │ │
│  └──────────────────────────────────────────────────────────┘ │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  Data Layer (Models)                                     │ │
│  │  - Tenant, EmailProvider, Outbox, Inbox                  │ │
│  │  - EmailTemplate, EmailLog, EmailWebhook                 │ │
│  │  - Attachment, InboundEmail, User                        │ │
│  └──────────────────────────────────────────────────────────┘ │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  Queue Workers & Commands                                │ │
│  │  - ProcessEmailQueue    - FetchInboundEmails             │ │
│  │  - StartRabbitMQListener                                  │ │
│  └──────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────────────────────────────┐
│                    DATABASE (MySQL)                              │
│  - tenants, email_providers, outbox, inbox                      │
│  - email_templates, email_logs, email_webhooks                 │
│  - attachments, inbound_emails, users                          │
└─────────────────────────────────────────────────────────────────┘
```

### **Technology Stack**

- **Framework:** Laravel 12.0
- **PHP Version:** 8.2+
- **Database:** MySQL 8.0+
- **Queue System:** RabbitMQ 3.8+ (with database fallback)
- **Email Providers:** AWS SES, Gmail (SMTP/IMAP), Postmark, Generic SMTP
- **Key Libraries:**
  - `aws/aws-sdk-php` - AWS SES integration
  - `php-amqplib/php-amqplib` - RabbitMQ client
  - `phpoffice/phpspreadsheet` - Excel/CSV processing

---

## 📁 Project Structure

### **Directory Organization**

```
email-microservice/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   │   ├── FetchInboundEmails.php      # Fetch emails via IMAP/POP3
│   │   ├── ProcessEmailQueue.php       # Process RabbitMQ queue
│   │   └── StartRabbitMQListener.php   # Start RabbitMQ listener
│   │
│   ├── Http/Controllers/          # HTTP Controllers
│   │   ├── Api/
│   │   │   ├── EmailController.php     # Main email API (33+ methods)
│   │   │   ├── InboundEmailController.php  # Inbound email management
│   │   │   └── WebhookController.php   # Webhook processing
│   │   ├── AuthController.php          # Authentication
│   │   └── OutboxController.php        # Outbox management
│   │
│   ├── Mail/
│   │   └── DirectEmail.php            # Direct email sending
│   │
│   ├── Models/                   # Eloquent Models (10 models)
│   │   ├── Tenant.php                 # Multi-tenant support
│   │   ├── EmailProvider.php          # Email provider configs
│   │   ├── Outbox.php                # Sent emails
│   │   ├── Inbox.php                 # Received emails
│   │   ├── InboundEmail.php          # Processed inbound emails
│   │   ├── EmailTemplate.php         # Email templates
│   │   ├── EmailLog.php              # Email activity logs
│   │   ├── EmailWebhook.php          # Webhook events
│   │   ├── Attachment.php            # Email attachments
│   │   └── User.php                  # System users
│   │
│   └── Services/                 # Business Logic Services (7 services)
│       ├── EmailService.php          # Core email sending
│       ├── RabbitMQService.php       # RabbitMQ integration
│       ├── EmailProcessingService.php # Email processing logic
│       ├── EmailFetcherService.php   # IMAP/POP3 fetching
│       ├── WebhookService.php        # Webhook processing
│       ├── MimeParserService.php     # MIME email parsing
│       └── FileStorageService.php    # File/attachment storage
│
├── config/                       # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── mail.php
│   ├── queue.php
│   └── rabbitmq.php              # RabbitMQ configuration
│
├── database/
│   ├── migrations/               # 17 database migrations
│   │   ├── create_tenants_table.php
│   │   ├── create_email_providers_table.php
│   │   ├── create_outbox_table.php
│   │   ├── create_inbox_table.php
│   │   ├── create_attachments_table.php
│   │   ├── create_email_templates_table.php
│   │   ├── create_email_logs_table.php
│   │   ├── create_email_webhooks_table.php
│   │   └── ... (tracking, bounce, queue columns)
│   │
│   └── seeders/                 # Database seeders
│       ├── DatabaseSeeder.php
│       ├── TenantSeeder.php
│       ├── EmailProviderSeeder.php
│       ├── EmailTemplateSeeder.php
│       ├── UserSeeder.php
│       └── BouncedEmailsSeeder.php
│
├── routes/
│   ├── api.php                  # API routes (50+ endpoints)
│   ├── web.php                  # Web routes (UI pages)
│   └── console.php              # Console routes
│
├── resources/
│   ├── views/                   # Blade templates
│   │   ├── emails/              # Email templates
│   │   ├── home.blade.php
│   │   ├── providers.blade.php
│   │   ├── email-logs.blade.php
│   │   ├── outbox.blade.php
│   │   └── ...
│   └── js/css/                  # Frontend assets
│
└── public/                      # Public web root
    └── index.php                # Application entry point
```

---

## 🗄️ Database Schema

### **Core Tables**

#### **1. tenants**
Multi-tenant support - each organization/company is a tenant.

| Column | Type | Description |
|--------|------|-------------|
| `tenant_id` | UUID (PK) | Unique tenant identifier |
| `tenant_name` | String | Organization name |
| `status` | Enum | active/inactive |

**Relationships:**
- Has many `EmailProvider`
- Has many `Outbox`
- Has many `Inbox`

#### **2. email_providers**
Email service provider configurations (AWS SES, Gmail, Postmark, etc.)

| Column | Type | Description |
|--------|------|-------------|
| `provider_id` | UUID (PK) | Unique provider identifier |
| `tenant_id` | UUID (FK) | Belongs to tenant |
| `provider_name` | String | Provider name (aws_ses, gmail, postmark) |
| `config_json` | JSON | Provider-specific configuration |
| `bounce_email` | String | Bounce handling email |
| `header_overrides` | JSON | Custom email headers |
| `is_active` | Boolean | Active status |

**config_json Structure:**
```json
{
  "protocol": "imap|pop3|smtp|api",
  "host": "smtp.gmail.com",
  "port": 587,
  "username": "user@example.com",
  "password": "encrypted_password",
  "encryption": "tls|ssl",
  "imap_host": "imap.gmail.com",
  "imap_port": 993,
  "aws_access_key": "...",
  "aws_secret_key": "...",
  "aws_region": "us-east-1"
}
```

#### **3. outbox**
All sent emails with complete tracking.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID (PK) | Email record ID |
| `tenant_id` | UUID (FK) | Tenant |
| `provider_id` | UUID (FK) | Email provider used |
| `user_id` | String | User who sent |
| `message_id` | String | Unique message identifier |
| `subject` | String | Email subject |
| `from` | String | Sender email |
| `to` | JSON | Recipients array |
| `cc` | JSON | CC recipients |
| `bcc` | JSON | BCC recipients |
| `body_format` | Enum | HTML, TEXT, EML, JSON |
| `body_content` | Text | Email body |
| `attachments` | JSON | Attachment metadata |
| `status` | Enum | pending, sent, failed, bounced, delivered |
| `error_message` | Text | Error details if failed |
| `provider_response` | JSON | Provider API response |
| `sent_at` | DateTime | When email was sent |
| `delivered_at` | DateTime | When email was delivered |
| `bounced_at` | DateTime | When email bounced |
| `source` | String | Source system (crm, api, queue) |
| `queue_name` | String | RabbitMQ queue name |
| `processing_method` | String | How email was processed |
| `queue_processed` | Boolean | Queue processing status |
| `queued_at` | DateTime | When queued |
| `processing_started_at` | DateTime | Processing start time |
| `processing_time_ms` | Integer | Processing duration |
| `delivery_time_ms` | Integer | Delivery duration |
| `retry_count` | Integer | Retry attempts |
| `metadata` | JSON | Additional metadata |
| `campaign_id` | String | Campaign identifier |
| `template_id` | String | Template used |
| `headers` | JSON | Email headers |
| `corrections` | JSON | Email corrections |
| `bounce_reason` | Text | Bounce reason |

#### **4. inbox**
Received emails (from IMAP/POP3).

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID (PK) | Email record ID |
| `tenant_id` | UUID (FK) | Tenant |
| `provider_id` | UUID (FK) | Provider that received |
| `user_id` | String | User email belongs to |
| `message_id` | String | Email message ID |
| `subject` | String | Email subject |
| `from` | String | Sender email |
| `to` | JSON | Recipients |
| `cc` | JSON | CC recipients |
| `bcc` | JSON | BCC recipients |
| `body_format` | Enum | HTML, TEXT, EML, JSON |
| `body_content` | Text | Email body |
| `attachments` | JSON | Attachment metadata |
| `received_at` | DateTime | When email was received |

#### **5. inbound_emails**
Processed inbound emails with thread tracking.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID (PK) | Record ID |
| `tenant_id` | UUID (FK) | Tenant |
| `provider_id` | UUID (FK) | Provider |
| `message_id` | String | Email message ID |
| `in_reply_to` | String | Original message ID (for replies) |
| `references` | String | Email thread references |
| `subject` | String | Email subject |
| `from_email` | String | Sender email |
| `from_name` | String | Sender name |
| `to_emails` | JSON | Recipients |
| `cc_emails` | JSON | CC recipients |
| `bcc_emails` | JSON | BCC recipients |
| `body_format` | Enum | HTML, TEXT, EML, JSON |
| `body_content` | Text | Email body |
| `attachments` | JSON | Attachment metadata |
| `headers` | JSON | Email headers |
| `status` | Enum | received, processed, queued, delivered, failed |
| `priority` | Enum | low, normal, high |
| `is_reply` | Boolean | Is this a reply? |
| `is_forward` | Boolean | Is this a forward? |
| `is_auto_reply` | Boolean | Is auto-reply? |
| `thread_id` | String | Conversation thread ID |
| `received_at` | DateTime | When received |
| `processed_at` | DateTime | When processed |
| `delivered_at` | DateTime | When delivered to CRM |
| `error_message` | Text | Error if failed |
| `provider_response` | JSON | Provider response |
| `metadata` | JSON | Additional data |
| `source` | String | Source system |
| `queue_name` | String | Queue name |
| `queue_processed` | Boolean | Queue status |
| `retry_count` | Integer | Retry attempts |

#### **6. email_templates**
Reusable email templates with variable substitution.

| Column | Type | Description |
|--------|------|-------------|
| `template_id` | String (PK) | Template identifier |
| `name` | String | Template name |
| `subject` | String | Email subject template |
| `html_content` | Text | HTML template |
| `text_content` | Text | Plain text template |
| `variables` | JSON | Available variables |
| `category` | String | Template category |
| `language` | String | Template language |
| `is_active` | Boolean | Active status |
| `metadata` | JSON | Additional metadata |

#### **7. email_logs**
Comprehensive audit log of all email activities.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BigInt (PK) | Log ID |
| `template_id` | String (FK) | Template used |
| `message_id` | String | Unique message ID |
| `provider_message_id` | String | Provider's message ID |
| `status` | Enum | sent, failed, bounced, delivered |
| `recipient_email` | String | Recipient |
| `sent_at` | DateTime | Send timestamp |
| `delivered_at` | DateTime | Delivery timestamp |
| `bounced_at` | DateTime | Bounce timestamp |
| `error_message` | Text | Error details |
| `provider_response` | JSON | Provider response |

#### **8. email_webhooks**
Webhook events from email providers (bounces, complaints, deliveries).

| Column | Type | Description |
|--------|------|-------------|
| `id` | BigInt (PK) | Webhook ID |
| `email_log_id` | BigInt (FK) | Related email log |
| `event_type` | String | bounce, complaint, delivery, etc. |
| `event_data` | JSON | Event payload |
| `processed` | Boolean | Processing status |
| `processed_at` | DateTime | Processing timestamp |

#### **9. attachments**
Email attachments with metadata.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID (PK) | Attachment ID |
| `email_id` | UUID (FK) | Related email |
| `email_type` | Enum | inbox, outbox |
| `filename` | String | Original filename |
| `mime_type` | String | MIME type |
| `storage_path` | String | File storage path |
| `file_size` | Integer | File size in bytes |
| `metadata` | JSON | Additional metadata |

#### **10. users**
System users with tenant association.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID (PK) | User ID |
| `tenant_id` | UUID (FK) | Tenant |
| `name` | String | User name |
| `email` | String | User email |
| `password` | String | Hashed password |
| `role` | Enum | admin, user |
| `is_active` | Boolean | Active status |
| `last_login_at` | DateTime | Last login |

---

## 🔄 Core Workflows

### **1. Outbound Email Flow (Via API)**

```
1. Client → POST /api/email/send
   └─> EmailController::sendEmail()
       └─> EmailService::sendEmail()
           ├─> Validate email data
           ├─> Load email template
           ├─> Render template with data
           ├─> Create EmailLog record
           ├─> Send via AWS SES
           ├─> Update EmailLog with result
           └─> Return response
```

### **2. Outbound Email Flow (Via RabbitMQ)**

```
1. CRM System → Publishes to RabbitMQ queue "email.send"
   └─> Message: {tenant_id, provider_id, from, to, subject, body, ...}

2. RabbitMQ Listener → Consumes message
   └─> ProcessEmailQueue command or RabbitMQService::processQueue()
       └─> EmailProcessingService::processOutboundEmail()
           ├─> Create Outbox record (status: pending)
           ├─> Load EmailProvider configuration
           ├─> Send email via provider (SMTP/API)
           ├─> Update Outbox (status: sent/failed)
           ├─> Create EmailLog entry
           └─> Return result
```

### **3. Inbound Email Flow**

```
1. Cron Job → Runs: php artisan email:fetch-inbound
   └─> FetchInboundEmails command
       └─> EmailFetcherService::fetchAllInboundEmails()
           ├─> Get all active EmailProviders
           ├─> For each provider:
           │   ├─> Connect via IMAP/POP3
           │   ├─> Fetch new emails
           │   ├─> Parse email (MimeParserService)
           │   ├─> Store attachments (FileStorageService)
           │   ├─> Create Inbox record
           │   └─> Create InboundEmail record
           └─> Return results
```

### **4. Webhook Processing Flow**

```
1. Email Provider (AWS SES) → POST /api/webhook/ses
   └─> WebhookController::handleSESWebhook()
       └─> WebhookService::processSESWebhook()
           ├─> Parse webhook payload
           ├─> Find EmailLog by provider_message_id
           ├─> Create EmailWebhook record
           ├─> Update EmailLog status
           ├─> Update Outbox status (if bounced/failed)
           └─> Return response
```

### **5. Manual Queue Processing**

```
1. Admin → POST /api/rabbitmq/process-queue
   └─> EmailController::processRabbitMQQueue()
       └─> RabbitMQService::processQueue()
           ├─> Connect to RabbitMQ
           ├─> Consume messages from queue
           ├─> Process each message
           ├─> Send emails
           └─> Return processing results
```

---

## 🛠️ Services Layer (Business Logic)

### **1. EmailService** (`app/Services/EmailService.php`)
**Purpose:** Core email sending service using AWS SES

**Key Methods:**
- `sendEmail(array $emailData)` - Send email with template
- `sendViaSES(array $emailContent)` - Send via AWS SES API
- `renderTemplate(EmailTemplate $template, array $data)` - Render template
- `prepareEmailContent()` - Prepare email with privacy headers
- `createEmailLog()` - Log email activity
- `updateEmailLogSuccess/Failure()` - Update log status

**Features:**
- Template rendering with Blade
- Privacy-focused headers (Message-ID, List-Unsubscribe, etc.)
- AWS SES Raw Email API integration
- Comprehensive error handling
- Email logging

### **2. RabbitMQService** (`app/Services/RabbitMQService.php`)
**Purpose:** RabbitMQ integration for queue-based email processing

**Key Methods:**
- `publishToQueue($queue, $data)` - Publish message to queue
- `processQueue($queueName, $maxMessages)` - Process queue messages
- `getQueueStatus()` - Get queue statistics
- `testConnection()` - Test RabbitMQ connection
- `processRealQueue()` - Real-time queue processing

**Queues:**
- `email.send` - Outbound email queue
- `email.sync.user` - User sync queue
- `email.inbound` - Inbound email queue

**Features:**
- Automatic connection management
- Mock mode fallback (if RabbitMQ unavailable)
- Queue status monitoring
- Message publishing and consumption

### **3. EmailProcessingService** (`app/Services/EmailProcessingService.php`)
**Purpose:** Process incoming and outgoing emails

**Key Methods:**
- `processIncomingEmail()` - Process received emails
- `processOutboundEmail()` - Process emails for sending
- `createInboxRecord()` - Create inbox entry
- `storeEmailAttachments()` - Store attachments
- `processBodyContent()` - Process email body

**Features:**
- MIME parsing integration
- Attachment handling
- Inline image processing
- Database transaction management

### **4. EmailFetcherService** (`app/Services/EmailFetcherService.php`)
**Purpose:** Fetch emails from IMAP/POP3 servers

**Key Methods:**
- `fetchAllInboundEmails()` - Fetch from all providers
- `fetchEmailsForProvider()` - Fetch from specific provider
- `hasInboundConfiguration()` - Check provider config
- `connectIMAP()` - Connect to IMAP server
- `connectPOP3()` - Connect to POP3 server

**Features:**
- Multi-provider support
- IMAP and POP3 protocols
- Automatic email parsing
- Error handling and retry logic

### **5. WebhookService** (`app/Services/WebhookService.php`)
**Purpose:** Process webhook events from email providers

**Key Methods:**
- `processSESWebhook()` - Process AWS SES webhooks
- `processSESRecord()` - Process single SES event
- `createWebhookRecord()` - Store webhook event
- `updateEmailStatus()` - Update email status from webhook

**Event Types:**
- `Bounce` - Email bounced
- `Complaint` - Email complaint
- `Delivery` - Email delivered
- `Send` - Email sent
- `Reject` - Email rejected

### **6. MimeParserService** (`app/Services/MimeParserService.php`)
**Purpose:** Parse MIME email messages

**Key Methods:**
- `parseEmail($rawEmail)` - Parse raw email
- `parseHeadersAndBody()` - Extract headers and body
- `parseMimeStructure()` - Parse MIME structure
- `extractAttachments()` - Extract attachments
- `extractInlineImages()` - Extract inline images
- `extractTextContent()` - Extract plain text
- `extractHtmlContent()` - Extract HTML content

**Features:**
- Full MIME parsing
- Multi-part message support
- Attachment extraction
- Inline image handling
- Character encoding support

### **7. FileStorageService** (`app/Services/FileStorageService.php`)
**Purpose:** Store email attachments and raw emails

**Key Methods:**
- `storeAttachment()` - Store email attachment
- `storeRawEmail()` - Store raw EML file
- `getFileUrl()` - Get file download URL
- `sanitizeFilename()` - Clean filename
- `generateUniqueFilename()` - Create unique filename

**Features:**
- Secure file storage
- Filename sanitization
- File size tracking
- Hash generation for deduplication

---

## 🎮 Controllers & API Endpoints

### **EmailController** (`app/Http/Controllers/Api/EmailController.php`)
**33+ public methods** handling all email operations

#### **Email Sending:**
- `POST /api/email/send` - Send email with template
- `POST /api/email/send-test-email` - Send test email
- `POST /api/rabbitmq/send-email` - Send via RabbitMQ queue

#### **Email Status & Tracking:**
- `GET /api/email/status/{messageId}` - Get email status
- `GET /api/email/logs` - Get email logs (filtered)
- `GET /api/email/stats` - Get email statistics
- `GET /api/email/tracking/stats` - Get tracking statistics
- `GET /api/email/tracking/recent` - Get recent emails
- `GET /api/email/tracking/performance` - Performance metrics
- `GET /api/email/tracking/analytics` - Email analytics

#### **Templates:**
- `GET /api/email/templates` - List all templates
- `GET /api/email/templates/{templateId}` - Get specific template

#### **Bounced Emails:**
- `GET /api/email/bounced` - Get bounced emails
- `POST /api/email/bounced/{id}/update-email` - Update bounced email
- `POST /api/email/bounced/{id}/requeue` - Requeue bounced email

#### **Providers:**
- `GET /api/email/providers` - List providers
- `POST /api/email/providers` - Create provider
- `GET /api/email/providers/{providerId}` - Get provider
- `PUT /api/email/providers/{providerId}` - Update provider
- `DELETE /api/email/providers/{providerId}` - Delete provider

#### **Tenants:**
- `GET /api/email/tenants` - List tenants

#### **RabbitMQ:**
- `GET /api/rabbitmq/status` - Queue status
- `GET /api/rabbitmq/queue-stats` - Queue statistics
- `POST /api/rabbitmq/process-queue` - Process queue manually

#### **Testing:**
- `POST /api/email/test/mime-parsing` - Test MIME parsing
- `POST /api/email/test/file-storage` - Test file storage

### **InboundEmailController** (`app/Http/Controllers/Api/InboundEmailController.php`)
**Purpose:** Manage inbound emails

**Endpoints:**
- `GET /api/email/inbound` - List inbound emails
- `POST /api/email/inbound` - Create inbound email record
- `GET /api/email/inbound/stats` - Inbound email statistics

### **WebhookController** (`app/Http/Controllers/Api/WebhookController.php`)
**Purpose:** Handle webhook events

**Endpoints:**
- `POST /api/webhook/ses` - AWS SES webhook
- `POST /api/webhook/{provider}` - Generic provider webhook
- `GET /api/webhook/stats` - Webhook statistics (protected)
- `GET /api/webhook/events` - Webhook events (protected)

### **OutboxController** (`app/Http/Controllers/OutboxController.php`)
**Purpose:** Manage outbox emails

**Endpoints:**
- `GET /api/outbox/emails` - List outbox emails
- `GET /api/outbox/emails/{id}` - Get email details
- `PATCH /api/outbox/emails/{id}/status` - Update status
- `POST /api/outbox/emails/{id}/resend` - Resend email
- `DELETE /api/outbox/emails/{id}` - Delete email
- `GET /api/outbox/stats` - Outbox statistics

---

## 🔧 Configuration Files

### **config/rabbitmq.php**
RabbitMQ connection and queue configuration.

```php
'host' => env('RABBITMQ_HOST', 'localhost'),
'port' => env('RABBITMQ_PORT', 5672),
'user' => env('RABBITMQ_USER', 'guest'),
'password' => env('RABBITMQ_PASSWORD', 'guest'),
'vhost' => env('RABBITMQ_VHOST', '/'),

'queues' => [
    'email_send' => 'email.send',
    'email_sync_user' => 'email.sync.user',
    'email_inbound' => 'email.inbound',
],
```

### **config/mail.php**
Email sending configuration (SMTP, AWS SES).

### **config/database.php**
Database connection settings.

### **config/queue.php**
Queue driver configuration (database, rabbitmq).

---

## 📊 Data Flow Examples

### **Example 1: Send Email via API**

```php
// 1. Client makes API request
POST /api/email/send
{
    "template_id": "welcome_user",
    "to": [{"email": "user@example.com", "name": "John"}],
    "data": {
        "user": {"name": "John", "email": "user@example.com"},
        "broker": {"name": "ForexPro"}
    }
}

// 2. EmailController::sendEmail()
//    └─> Validates request
//    └─> Calls EmailService::sendEmail()

// 3. EmailService::sendEmail()
//    └─> Loads EmailTemplate
//    └─> Renders template with data
//    └─> Creates EmailLog record
//    └─> Sends via AWS SES
//    └─> Updates EmailLog with result

// 4. Returns response
{
    "success": true,
    "message_id": "uuid-here",
    "status": "sent"
}
```

### **Example 2: Send Email via RabbitMQ**

```php
// 1. CRM publishes to RabbitMQ
RabbitMQService::publishToQueue('email.send', [
    'tenant_id' => 'uuid',
    'provider_id' => 'uuid',
    'from' => 'noreply@company.com',
    'to' => ['user@example.com'],
    'subject' => 'Welcome',
    'body_format' => 'HTML',
    'body_content' => '<h1>Hello</h1>'
]);

// 2. Queue worker consumes message
ProcessEmailQueue command or RabbitMQService::processQueue()

// 3. EmailProcessingService::processOutboundEmail()
//    └─> Creates Outbox record (status: pending)
//    └─> Loads EmailProvider config
//    └─> Sends email via provider
//    └─> Updates Outbox (status: sent/failed)
//    └─> Creates EmailLog entry

// 4. Webhook updates status (if bounced/delivered)
WebhookService::processSESWebhook()
//    └─> Updates Outbox status
//    └─> Updates EmailLog status
```

### **Example 3: Fetch Inbound Emails**

```php
// 1. Cron job runs
php artisan email:fetch-inbound

// 2. FetchInboundEmails command
//    └─> EmailFetcherService::fetchAllInboundEmails()

// 3. For each active provider:
//    └─> Connect via IMAP/POP3
//    └─> Fetch new emails
//    └─> MimeParserService::parseEmail()
//    └─> FileStorageService::storeAttachment()
//    └─> Create Inbox record
//    └─> Create InboundEmail record

// 4. Results logged
```

---

## 🔐 Security & Privacy Features

### **1. Multi-Tenant Isolation**
- Each tenant has isolated data
- Tenant-based queries and scopes
- UUID-based identifiers

### **2. Privacy Headers**
- Custom Message-ID generation
- List-Unsubscribe headers
- X-Entity-Ref-ID for tracking
- Infrastructure masking

### **3. Secure Configuration**
- Encrypted provider credentials (stored in JSON)
- Environment-based configuration
- Secure file storage

### **4. Input Validation**
- Comprehensive request validation
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)

---

## 📈 Monitoring & Logging

### **Logging Points:**
1. **Email Sending:** All send attempts logged
2. **Queue Processing:** Queue operations logged
3. **Webhook Events:** All webhook events logged
4. **Errors:** Comprehensive error logging
5. **Performance:** Processing time tracking

### **Log Files:**
- `storage/logs/laravel.log` - Application logs
- `EmailMicroservice.log` - Implementation log

### **Database Logging:**
- `email_logs` - All email activities
- `email_webhooks` - Webhook events
- `outbox` - Sent email tracking
- `inbound_emails` - Received email tracking

---

## 🚀 Usage Examples

### **1. Send Email via API**

```bash
curl -X POST http://localhost:8000/api/email/send \
  -H "Content-Type: application/json" \
  -d '{
    "template_id": "welcome_user",
    "to": [{"email": "user@example.com", "name": "John"}],
    "data": {
        "user": {"name": "John", "email": "user@example.com"},
        "broker": {"name": "ForexPro"}
    }
  }'
```

### **2. Send Email via RabbitMQ**

```bash
curl -X POST http://localhost:8000/api/rabbitmq/send-email \
  -H "Content-Type: application/json" \
  -d '{
    "tenant_id": "tenant-uuid",
    "provider_id": "provider-uuid",
    "from": "noreply@company.com",
    "to": ["user@example.com"],
    "subject": "Test Email",
    "body_format": "HTML",
    "body_content": "<h1>Hello World</h1>"
  }'
```

### **3. Check Queue Status**

```bash
curl http://localhost:8000/api/rabbitmq/status
```

### **4. Fetch Inbound Emails**

```bash
php artisan email:fetch-inbound
```

### **5. Process Queue**

```bash
php artisan email:listen-queue
```

---

## 🔄 Integration Points

### **1. CRM Integration (RabbitMQ)**
- CRM publishes to `email.send` queue
- Email microservice consumes and processes
- Status updates via webhooks

### **2. Email Provider Integration**
- **AWS SES:** API-based sending, webhook events
- **Gmail:** SMTP sending, IMAP receiving
- **Postmark:** API-based sending
- **Generic SMTP:** Standard SMTP protocol

### **3. Webhook Integration**
- AWS SES webhooks for delivery events
- Generic webhook support for other providers
- Automatic status updates

---

## 📝 Key Features Summary

### ✅ **Implemented Features:**

1. **Multi-Tenant Support** - Complete tenant isolation
2. **Multiple Email Providers** - AWS SES, Gmail, Postmark, Generic SMTP
3. **RabbitMQ Integration** - Queue-based processing
4. **Inbound Email Fetching** - IMAP/POP3 support
5. **Email Templates** - Dynamic template rendering
6. **Attachment Handling** - File storage and management
7. **Webhook Processing** - Delivery event handling
8. **Comprehensive Logging** - Full audit trail
9. **Email Tracking** - Status, analytics, performance metrics
10. **Bounce Handling** - Bounce detection and management
11. **Thread Tracking** - Email conversation threads
12. **MIME Parsing** - Full email parsing support
13. **Privacy Features** - Infrastructure masking, custom headers
14. **API-First Design** - RESTful API endpoints
15. **Queue Workers** - Background processing

---

## 🎯 Project Purpose

This email microservice is designed to:

1. **Decouple Email Functionality** - Separate email operations from main CRM
2. **Support Multiple Tenants** - Serve multiple organizations
3. **Handle High Volume** - Queue-based processing for scalability
4. **Provide Flexibility** - Support multiple email providers
5. **Ensure Reliability** - Comprehensive error handling and logging
6. **Maintain Privacy** - Privacy-focused email headers and infrastructure masking
7. **Enable Monitoring** - Full tracking and analytics

---

## 📚 File Usage Guide

### **For Sending Emails:**
- Use `EmailService` for direct sending
- Use `RabbitMQService` for queue-based sending
- Use `EmailController::sendEmail()` for API sending

### **For Receiving Emails:**
- Use `EmailFetcherService` to fetch from IMAP/POP3
- Use `EmailProcessingService` to process incoming emails
- Use `MimeParserService` to parse email content

### **For Queue Processing:**
- Run `php artisan email:listen-queue` for continuous processing
- Use `RabbitMQService::processQueue()` for manual processing
- Monitor via `GET /api/rabbitmq/status`

### **For Webhooks:**
- Configure webhook endpoints in email providers
- Use `WebhookController` to receive events
- Use `WebhookService` to process events

---

## 🔍 Quick Reference

### **Main Entry Points:**
- **API:** `routes/api.php`
- **Web UI:** `routes/web.php`
- **Commands:** `app/Console/Commands/`
- **Services:** `app/Services/`
- **Models:** `app/Models/`

### **Configuration:**
- **Environment:** `.env` file
- **RabbitMQ:** `config/rabbitmq.php`
- **Mail:** `config/mail.php`
- **Database:** `config/database.php`

### **Database:**
- **Migrations:** `database/migrations/`
- **Seeders:** `database/seeders/`

---

## 🎉 Summary

This is a **production-ready, enterprise-grade email microservice** with:
- ✅ Complete multi-tenant support
- ✅ Multiple email provider integration
- ✅ RabbitMQ queue processing
- ✅ Inbound/outbound email handling
- ✅ Comprehensive logging and tracking
- ✅ Webhook event processing
- ✅ Full API coverage
- ✅ Privacy and security features

The project is well-structured, follows Laravel best practices, and is ready for production deployment with proper configuration.

