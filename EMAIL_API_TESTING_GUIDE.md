# 📧 Email API Testing Guide - Frontend Links & Pages

## 🚨 **Important: Fix MySQL Connection First**

Before testing, ensure MySQL is running in XAMPP:
1. Open XAMPP Control Panel
2. Start MySQL service
3. Or use SQLite by changing `DB_CONNECTION=sqlite` in `.env`

---

## 🌐 **Frontend Test Pages**

### **1. Main Email API Test Page** ⭐
**URL:** `http://localhost:8000/email-api-test`

This is the **main testing page** with interactive forms for all features:
- ✅ Get Emails (Sent/Received/Both) with filters
- ✅ Get Replied Emails
- ✅ Send Email via RabbitMQ (Template-Based)
- ✅ Quick API links

**Features:**
- Real-time API testing
- Form validation
- JSON response display
- Easy parameter input

---

## 📋 **Direct API Endpoints**

### **1. Get Emails (Sent/Received/Both)**

**Endpoint:** `GET /api/email/emails`

**Required Parameters:**
- `tenant_id` - Your tenant UUID
- `type` - `sent` | `received` | `both`

**Example Links:**

**Get Both Sent & Received:**
```
http://localhost:8000/api/email/emails?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&type=both&page=1&per_page=20
```

**Get Sent Emails Only:**
```
http://localhost:8000/api/email/emails?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&type=sent&status=sent&page=1&per_page=20
```

**Get Received Emails Only:**
```
http://localhost:8000/api/email/emails?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&type=received&status=processed&page=1&per_page=20
```

**With Filters:**
```
http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=both&status=sent&search=test&from_email=sender@example.com&date_from=2025-01-01&date_to=2025-12-31&page=1&per_page=20
```

**Available Filters:**
- `status` - pending, sent, failed, bounced, delivered, new, processed, queued
- `search` - Search in subject, from, to fields
- `from_email` - Filter by sender
- `to_email` - Filter by recipient
- `subject` - Filter by subject
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)
- `provider_id` - Filter by provider
- `user_id` - Filter by user
- `page` - Page number (default: 1)
- `per_page` - Items per page (1-100, default: 20)

---

### **2. Get Replied Emails**

**Endpoint:** `GET /api/email/replies`

**Required Parameters:**
- `tenant_id` - Your tenant UUID

**Example Links:**

**Basic:**
```
http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2
```

**With Filters:**
```
http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&status=processed&from_email=sender@example.com&date_from=2025-01-01&date_to=2025-12-31&page=1&per_page=20
```

**Available Filters:**
- `status` - new, processed, queued, delivered, failed
- `in_reply_to` - Specific message ID
- `thread_id` - Conversation thread ID
- `from_email` - Filter by sender
- `date_from` - Start date
- `date_to` - End date
- `page` - Page number
- `per_page` - Items per page

---

## 📎 **3. How to Pass Files (Not via RabbitMQ)**

### **Method: Pass File URLs in Request**

Files are passed as **URLs** in the attachment array, NOT as file content.

**Example Request:**
```json
POST /api/rabbitmq/send-email
{
    "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
    "provider_id": "0198a819-e5d3-703a-a39a-1b77e3ece687",
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
        },
        {
            "url": "https://example.com/files/image.jpg",
            "filename": "image.jpg",
            "mime_type": "image/jpeg"
        }
    ]
}
```

**Important Notes:**
- ✅ Files must be accessible via HTTP/HTTPS URLs
- ✅ Maximum file size: 25MB per file
- ✅ Files are fetched by the Email Service during processing
- ✅ SSRF protection is included
- ❌ Cannot pass file content directly through RabbitMQ

**Test via Frontend:**
- Go to: `http://localhost:8000/email-api-test`
- Use the "Send Email via RabbitMQ (Template-Based)" section
- Enter attachment URLs in JSON format

---

## 📝 **4. Template-Based Email Body Building**

### **How It Works:**

Instead of passing `body_content` in RabbitMQ, you pass:
- `template_id` - Template identifier from database
- `template_data` - Variables to populate the template

**Example Request:**
```json
POST /api/rabbitmq/send-email
{
    "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
    "provider_id": "0198a819-e5d3-703a-a39a-1b77e3ece687",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "subject": "Welcome Email",  // Optional if template has subject
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe",
        "company": "Example Corp",
        "email": "john@example.com"
    }
}
```

**What Happens:**
1. ✅ Request sent to RabbitMQ with `template_id` and `template_data`
2. ✅ Email Service fetches template from database
3. ✅ Template is rendered with `template_data` using Blade engine
4. ✅ Email body is built from rendered template
5. ✅ Email is sent with the rendered content

**Test via Frontend:**
- Go to: `http://localhost:8000/email-api-test`
- Use the "Send Email via RabbitMQ (Template-Based)" section
- Enter `template_id` and `template_data` (JSON format)

---

## 🧪 **Testing Checklist**

### **Get Emails API:**
- [ ] Test with `type=sent`
- [ ] Test with `type=received`
- [ ] Test with `type=both`
- [ ] Test pagination (page, per_page)
- [ ] Test filters (status, search, dates, etc.)
- [ ] Verify tenant_id is required

### **Get Replied Emails API:**
- [ ] Test basic request with tenant_id
- [ ] Test with filters (status, dates, etc.)
- [ ] Test pagination
- [ ] Verify tenant_id is required

### **File Attachments:**
- [ ] Test with single file URL
- [ ] Test with multiple file URLs
- [ ] Verify files are fetched and attached
- [ ] Test with invalid URLs (should handle gracefully)

### **Template-Based Emails:**
- [ ] Test with valid template_id
- [ ] Test with template_data variables
- [ ] Verify template is fetched from DB
- [ ] Verify email body is built from template
- [ ] Test with invalid template_id (should error)

---

## 🔗 **Quick Access Links**

Once your server is running and MySQL is connected:

1. **Main Test Page:**
   ```
   http://localhost:8000/email-api-test
   ```

2. **Health Check:**
   ```
   http://localhost:8000/api/health
   ```

3. **API Documentation:**
   ```
   http://localhost:8000/api/
   ```

---

## 📖 **Additional Resources**

- **API Reference:** See `CLIENT_DELIVERY/API_DOCUMENTATION/API_REFERENCE.md`
- **RabbitMQ Integration:** See `CLIENT_DELIVERY/API_DOCUMENTATION/RABBITMQ_INTEGRATION.md`
- **Template Flow:** See `TEMPLATE_BASED_EMAIL_FLOW.md`
- **File Attachments:** See `FILE_ATTACHMENT_EXPLANATION.md`

---

**Note:** Replace `YOUR_TENANT_ID` with your actual tenant UUID from the database.

