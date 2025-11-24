# 📧 Real API Endpoints - Direct URLs

## ✅ **All Your Requested Features - Real API Endpoints**

---

## 1️⃣ **API to Get (Sent/Received) Emails with Pagination & Filters**

### **Endpoint:** `GET /api/email/emails`

**Required Parameters:**
- `tenant_id` (string, required) - Your tenant UUID

**Required Parameters:**
- `type` (enum, required) - `sent` | `received` | `both`

**Optional Parameters:**
- `status` - Filter by status (pending, sent, failed, bounced, delivered, new, processed, queued)
- `search` - Search in subject, from, to fields
- `from_email` - Filter by sender email
- `to_email` - Filter by recipient email
- `subject` - Filter by subject (partial match)
- `date_from` - Filter emails from this date (YYYY-MM-DD)
- `date_to` - Filter emails to this date (YYYY-MM-DD)
- `provider_id` - Filter by email provider
- `user_id` - Filter by user ID
- `page` - Page number (default: 1)
- `per_page` - Items per page (1-100, default: 20)

### **Direct API URLs:**

**Get Both Sent & Received:**
```
http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=both&page=1&per_page=20
```

**Get Sent Emails Only:**
```
http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=sent&status=sent&page=1&per_page=20
```

**Get Received Emails Only:**
```
http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=received&status=processed&page=1&per_page=20
```

**With All Filters:**
```
http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=both&status=sent&search=test&from_email=sender@example.com&to_email=recipient@example.com&subject=Welcome&date_from=2025-01-01&date_to=2025-12-31&provider_id=PROVIDER_ID&user_id=USER_ID&page=1&per_page=20
```

**Example Response:**
```json
{
    "success": true,
    "data": {
        "sent": {
            "data": [...],
            "pagination": {
                "current_page": 1,
                "last_page": 5,
                "per_page": 20,
                "total": 100,
                "from": 1,
                "to": 20
            }
        },
        "received": {
            "data": [...],
            "pagination": {...}
        }
    },
    "filters": {...}
}
```

---

## 2️⃣ **API to Fetch Replied Emails**

### **Endpoint:** `GET /api/email/replies`

**Required Parameters:**
- `tenant_id` (string, required) - Your tenant UUID

**Optional Parameters:**
- `status` - Filter by status (new, processed, queued, delivered, failed)
- `in_reply_to` - Filter by specific message ID
- `thread_id` - Filter by conversation thread
- `from_email` - Filter by sender email
- `date_from` - Filter emails from this date (YYYY-MM-DD)
- `date_to` - Filter emails to this date (YYYY-MM-DD)
- `page` - Page number (default: 1)
- `per_page` - Items per page (1-100, default: 20)

### **Direct API URLs:**

**Basic:**
```
http://localhost:8000/api/email/replies?tenant_id=YOUR_TENANT_ID
```

**With Filters:**
```
http://localhost:8000/api/email/replies?tenant_id=YOUR_TENANT_ID&status=processed&from_email=sender@example.com&date_from=2025-01-01&date_to=2025-12-31&page=1&per_page=20
```

**With Thread ID:**
```
http://localhost:8000/api/email/replies?tenant_id=YOUR_TENANT_ID&thread_id=THREAD_ID&page=1&per_page=20
```

**Example Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "...",
            "is_reply": true,
            "thread_emails": [...],
            "replied_to_outbound": {...}
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 3,
        "per_page": 20,
        "total": 50,
        "from": 1,
        "to": 20
    },
    "filters": {...}
}
```

---

## 3️⃣ **How to Pass Files (Not via RabbitMQ)**

### **Endpoint:** `POST /api/rabbitmq/send-email`

**Important:** Files are passed as **URLs** in the request, NOT as file content through RabbitMQ.

### **Request Format:**

```json
POST http://localhost:8000/api/rabbitmq/send-email
Content-Type: application/json

{
    "tenant_id": "YOUR_TENANT_ID",
    "provider_id": "YOUR_PROVIDER_ID",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "cc": ["cc@example.com"],
    "bcc": ["bcc@example.com"],
    "subject": "Email Subject",
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe",
        "company": "Example Corp"
    },
    "attachments": [
        {
            "url": "https://example.com/files/document.pdf",
            "filename": "document.pdf",
            "mime_type": "application/pdf"
        },
        {
            "url": "https://example.com/files/image.jpg",
            "filename": "image.jpg",
            "mime_type": "image/jpeg"
        }
    ],
    "priority": "normal",
    "scheduled_at": "2025-12-01T10:00:00Z"
}
```

### **How It Works:**
1. ✅ You pass file **URLs** in the `attachments` array
2. ✅ RabbitMQ receives the message with URLs (not file content)
3. ✅ Email Service fetches files from URLs during processing
4. ✅ Files are attached to the email automatically
5. ✅ Maximum file size: 25MB per file
6. ✅ SSRF protection included

### **cURL Example:**
```bash
curl -X POST http://localhost:8000/api/rabbitmq/send-email \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tenant_id": "YOUR_TENANT_ID",
    "provider_id": "YOUR_PROVIDER_ID",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe"
    },
    "attachments": [
        {
            "url": "https://example.com/files/document.pdf",
            "filename": "document.pdf",
            "mime_type": "application/pdf"
        }
    ]
}'
```

---

## 4️⃣ **Template-Based Email Body Building**

### **Endpoint:** `POST /api/rabbitmq/send-email`

**Important:** Instead of passing `body_content`, you pass `template_id` and `template_data`. The email body is built automatically from the template.

### **Request Format:**

```json
POST http://localhost:8000/api/rabbitmq/send-email
Content-Type: application/json

{
    "tenant_id": "YOUR_TENANT_ID",
    "provider_id": "YOUR_PROVIDER_ID",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "subject": "Welcome Email",
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe",
        "company": "Example Corp",
        "email": "john@example.com",
        "activation_link": "https://example.com/activate/123"
    }
}
```

### **How It Works:**
1. ✅ You pass `template_id` (template name/ID from database)
2. ✅ You pass `template_data` (variables to populate the template)
3. ✅ RabbitMQ receives template reference (NOT body content)
4. ✅ Email Service fetches template from database using `template_id`
5. ✅ Template is rendered with `template_data` using Blade engine
6. ✅ Email body is built automatically from rendered template
7. ✅ Email is sent with the rendered content

### **What NOT to Include:**
- ❌ Do NOT include `body_content`
- ❌ Do NOT include `body_format`
- ❌ Do NOT include full HTML/text in the request

### **cURL Example:**
```bash
curl -X POST http://localhost:8000/api/rabbitmq/send-email \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tenant_id": "YOUR_TENANT_ID",
    "provider_id": "YOUR_PROVIDER_ID",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe",
        "company": "Example Corp"
    }
}'
```

### **Combined Example (Template + Files):**
```json
POST http://localhost:8000/api/rabbitmq/send-email
Content-Type: application/json

{
    "tenant_id": "YOUR_TENANT_ID",
    "provider_id": "YOUR_PROVIDER_ID",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe",
        "company": "Example Corp"
    },
    "attachments": [
        {
            "url": "https://example.com/files/document.pdf",
            "filename": "document.pdf",
            "mime_type": "application/pdf"
        }
    ]
}
```

---

## 📋 **Complete API Reference**

### **Base URL:**
```
http://localhost:8000/api
```

### **All Endpoints:**

1. **Get Emails (Sent/Received/Both)**
   ```
   GET /api/email/emails?tenant_id=XXX&type=both
   ```

2. **Get Replied Emails**
   ```
   GET /api/email/replies?tenant_id=XXX
   ```

3. **Send Email (Template-Based with Files)**
   ```
   POST /api/rabbitmq/send-email
   ```

4. **Health Check**
   ```
   GET /api/health
   ```

---

## 🔧 **Testing with Postman/Thunder Client**

### **1. Get Emails:**
- **Method:** GET
- **URL:** `http://localhost:8000/api/email/emails`
- **Query Params:**
  - `tenant_id`: YOUR_TENANT_ID
  - `type`: both
  - `page`: 1
  - `per_page`: 20

### **2. Get Replied Emails:**
- **Method:** GET
- **URL:** `http://localhost:8000/api/email/replies`
- **Query Params:**
  - `tenant_id`: YOUR_TENANT_ID
  - `page`: 1
  - `per_page`: 20

### **3. Send Email:**
- **Method:** POST
- **URL:** `http://localhost:8000/api/rabbitmq/send-email`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Body (JSON):**
```json
{
    "tenant_id": "YOUR_TENANT_ID",
    "provider_id": "YOUR_PROVIDER_ID",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe"
    },
    "attachments": [
        {
            "url": "https://example.com/files/document.pdf",
            "filename": "document.pdf",
            "mime_type": "application/pdf"
        }
    ]
}
```

---

## 📝 **Important Notes**

1. **Tenant ID is Required:** All endpoints require `tenant_id` as a mandatory parameter
2. **Template ID Must Exist:** The `template_id` must exist in the `email_templates` table
3. **File URLs Must Be Accessible:** Attachment URLs must be publicly accessible via HTTP/HTTPS
4. **No Body Content:** Do NOT pass `body_content` when using templates
5. **File Size Limit:** Maximum 25MB per file
6. **Pagination:** Default page size is 20, maximum is 100 per page

---

**Last Updated:** 2025-11-24

