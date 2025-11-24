# 🎯 Frontend Testing Links - Complete Guide

## ✅ **All Your Requested Features - Where to Test**

---

## 1️⃣ **API to Get (Sent/Received) Emails with Pagination & Filters**

### **Main Test Page (Recommended):**
```
http://localhost:8000/email-api-test
```
- Scroll to section: **"1. Get Emails (Sent/Received/Both)"**
- Fill in `tenant_id` (required) and `type` (sent|received|both)
- Add filters as needed
- Click "Get Emails" button

### **Direct API Endpoints:**

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
http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=both&status=sent&search=test&from_email=sender@example.com&to_email=recipient@example.com&date_from=2025-01-01&date_to=2025-12-31&page=1&per_page=20
```

### **Existing Web Pages:**
- **Outbox (Sent Emails):** `http://localhost:8000/outbox`
- **Inbound Emails (Received):** `http://localhost:8000/inbound-emails`

**Note:** These pages require authentication and MySQL to be running.

---

## 2️⃣ **API to Fetch Replied Emails**

### **Main Test Page (Recommended):**
```
http://localhost:8000/email-api-test
```
- Scroll to section: **"2. Get Replied Emails"**
- Fill in `tenant_id` (required)
- Add optional filters
- Click "Get Replied Emails" button

### **Direct API Endpoint:**

**Basic:**
```
http://localhost:8000/api/email/replies?tenant_id=YOUR_TENANT_ID
```

**With Filters:**
```
http://localhost:8000/api/email/replies?tenant_id=YOUR_TENANT_ID&status=processed&from_email=sender@example.com&date_from=2025-01-01&date_to=2025-12-31&page=1&per_page=20
```

### **Existing Web Page:**
- **Replied Emails:** `http://localhost:8000/replied-emails`

---

## 3️⃣ **How to Pass Files (Not via RabbitMQ)**

### **Main Test Page (Recommended):**
```
http://localhost:8000/email-api-test
```
- Scroll to section: **"3. Send Email via RabbitMQ (Template-Based)"**
- Fill in all required fields
- In **"Attachment URLs"** field, enter JSON array:
```json
[
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
```

### **How It Works:**
1. ✅ Files are passed as **URLs** (not file content)
2. ✅ Email Service fetches files from URLs during processing
3. ✅ Files are attached to the email automatically
4. ✅ Maximum file size: 25MB per file
5. ✅ SSRF protection included

### **API Endpoint:**
```
POST http://localhost:8000/api/rabbitmq/send-email
```

**Request Body:**
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

## 4️⃣ **Template-Based Email Body Building**

### **Main Test Page (Recommended):**
```
http://localhost:8000/email-api-test
```
- Scroll to section: **"3. Send Email via RabbitMQ (Template-Based)"**
- Fill in:
  - **Template ID:** e.g., `welcome-template`
  - **Template Data (JSON):**
  ```json
  {
    "name": "John Doe",
    "company": "Example Corp",
    "email": "john@example.com"
  }
  ```

### **How It Works:**
1. ✅ Pass `template_id` and `template_data` (NOT `body_content`)
2. ✅ RabbitMQ receives template reference
3. ✅ Email Service fetches template from database
4. ✅ Template is rendered with `template_data` using Blade engine
5. ✅ Email body is built automatically from rendered template

### **API Endpoint:**
```
POST http://localhost:8000/api/rabbitmq/send-email
```

**Request Body (Template-Based):**
```json
{
    "tenant_id": "YOUR_TENANT_ID",
    "provider_id": "YOUR_PROVIDER_ID",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "subject": "Welcome Email",
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe",
        "company": "Example Corp"
    }
}
```

**Note:** Do NOT include `body_content` or `body_format` - these are built from the template.

---

## 🔧 **Troubleshooting**

### **If you see "Error loading emails":**

1. **Check MySQL is Running:**
   - Open XAMPP Control Panel
   - Start MySQL service
   - Wait for it to turn green

2. **Check Database Connection:**
   - Verify `.env` file has correct database credentials
   - Run: `php artisan migrate` (if needed)

3. **Check Tenant ID:**
   - Make sure you're using a valid `tenant_id` from your database
   - You can check tenants at: `http://localhost:8000/api/email/tenants`

4. **Clear Cache:**
   ```bash
   cd email-microservice
   php artisan config:clear
   php artisan cache:clear
   ```

---

## 📋 **Quick Reference - All Links**

### **Test Pages:**
- ✅ **Main API Test Page:** `http://localhost:8000/email-api-test` ⭐ **USE THIS**
- **Outbox (Sent):** `http://localhost:8000/outbox`
- **Inbound Emails (Received):** `http://localhost:8000/inbound-emails`
- **Replied Emails:** `http://localhost:8000/replied-emails`
- **RabbitMQ Test:** `http://localhost:8000/rabbitmq-test`

### **API Endpoints:**
- **Get Emails:** `GET /api/email/emails?tenant_id=XXX&type=both`
- **Get Replied Emails:** `GET /api/email/replies?tenant_id=XXX`
- **Send Email (Template):** `POST /api/rabbitmq/send-email`
- **Health Check:** `GET /api/health`

### **Documentation:**
- **API Testing Guide:** `EMAIL_API_TESTING_GUIDE.md`
- **Changes Summary:** `EMAIL_API_CHANGES_SUMMARY.md`

---

## 🎯 **Recommended Testing Flow**

1. **Start with Main Test Page:**
   ```
   http://localhost:8000/email-api-test
   ```

2. **Test Get Emails API:**
   - Use section "1. Get Emails"
   - Try `type=sent`, `type=received`, and `type=both`
   - Test with different filters

3. **Test Replied Emails API:**
   - Use section "2. Get Replied Emails"
   - Test with filters

4. **Test Template-Based Email:**
   - Use section "3. Send Email via RabbitMQ"
   - Enter `template_id` and `template_data`
   - Add file URLs in attachments field

5. **Verify Results:**
   - Check RabbitMQ queue processing
   - Check outbox table in database
   - Verify email was sent

---

**Last Updated:** 2025-11-24

