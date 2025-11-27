# 🎯 Frontend Pages for Client Demo

## ✅ Frontend Links to Show in Demo

Based on your requested changes, here are the **frontend pages** (not APIs) you should demonstrate to your client:

---

## 1️⃣ **API to Get (Sent/Received) Emails with Pagination & Filters**

### **Frontend Pages to Show:**

#### **A. Outbox Page (Sent Emails)**
```
http://localhost:8000/outbox
```
**What to demonstrate:**
- ✅ Shows all sent emails with pagination
- ✅ Filter by tenant (required field)
- ✅ Filter by status, date range, sender, recipient
- ✅ Search functionality
- ✅ Pagination controls

#### **B. Inbound Emails Page (Received Emails)**
```
http://localhost:8000/inbound-emails
```
**What to demonstrate:**
- ✅ Shows all received emails with pagination
- ✅ Filter by tenant (required field)
- ✅ Filter by status, date range, sender
- ✅ Search functionality
- ✅ Pagination controls

**Note:** Both pages require authentication. Make sure you're logged in before the demo.

---

## 2️⃣ **API to Fetch Replied Emails**

### **Frontend Page to Show:**

#### **Replied Emails Page**
```
http://localhost:8000/replied-emails
```
**What to demonstrate:**
- ✅ Shows all replied emails
- ✅ Filter by tenant (required field)
- ✅ Filter by date range, sender, thread ID
- ✅ Shows email threads and conversation history
- ✅ Pagination support
- ✅ Links replies to original outbound emails

---

## 3️⃣ **How to Pass Files (Without RabbitMQ)**

### **Frontend Page to Show:**

#### **RabbitMQ Test Page (Template-Based Email with Attachments)**
```
http://localhost:8000/rabbitmq-test
```
**What to demonstrate:**
- ✅ Scroll to the email sending form
- ✅ Show the **"Attachment URLs"** field
- ✅ Explain that files are passed as **URLs** (not file content)
- ✅ Demonstrate entering attachment URLs in JSON format:
  ```json
  [
    {
      "url": "https://example.com/files/document.pdf",
      "filename": "document.pdf",
      "mime_type": "application/pdf"
    }
  ]
  ```
- ✅ Explain that Email Service fetches files from URLs during processing
- ✅ Show that files are attached automatically when email is sent

**Key Points to Highlight:**
- Files are NOT sent through RabbitMQ (only URLs)
- Email Service downloads files from URLs when processing
- Maximum file size: 25MB per file
- SSRF protection included

---

## 4️⃣ **Template-Based Email Body Building**

### **Frontend Page to Show:**

#### **RabbitMQ Test Page (Template-Based Email)**
```
http://localhost:8000/rabbitmq-test
```
**What to demonstrate:**
- ✅ Show the email sending form
- ✅ **Template ID field:** Select a template from dropdown
- ✅ **Template Data field:** Enter JSON with template variables:
  ```json
  {
    "name": "John Doe",
    "company": "Example Corp",
    "email": "john@example.com"
  }
  ```
- ✅ **Important:** Show that there is NO "Body Content" field
- ✅ Explain the flow:
  1. You pass `template_id` and `template_data` (NOT `body_content`)
  2. RabbitMQ receives only template reference
  3. Email Service fetches template from database
  4. Template is rendered with `template_data` using Blade engine
  5. Email body is built automatically from rendered template

**Key Points to Highlight:**
- ✅ No email body is passed through RabbitMQ
- ✅ Only template ID and parameters are sent
- ✅ Email body is built in Email Service by fetching template from DB
- ✅ Smaller queue messages (better performance)
- ✅ Templates can be updated in DB without re-queuing emails

---

## 📋 **Complete Demo Flow**

### **Recommended Order:**

1. **Start with Outbox Page** (`/outbox`)
   - Show sent emails with filters and pagination
   - Demonstrate tenant filtering (required field)
   - Show different filter options

2. **Show Inbound Emails Page** (`/inbound-emails`)
   - Show received emails with filters and pagination
   - Demonstrate tenant filtering (required field)
   - Compare with outbox to show both sent and received

3. **Show Replied Emails Page** (`/replied-emails`)
   - Show replied emails
   - Demonstrate thread view
   - Show how replies link to original emails

4. **Show RabbitMQ Test Page** (`/rabbitmq-test`)
   - **Part A:** Template-Based Email
     - Show template selection
     - Show template data input
     - Explain that body is built from template (not passed)
   - **Part B:** File Attachments
     - Show attachment URLs field
     - Explain files are passed as URLs (not through RabbitMQ)
     - Show how Email Service fetches files

---

## 🔐 **Authentication Required**

**Important:** All these pages require authentication. Make sure you:
1. Log in first at: `http://localhost:8000/login`
2. Or ensure you have a valid session before the demo

---

## 🎯 **Quick Reference - All Frontend Links**

| Feature | Frontend Page | URL |
|---------|--------------|-----|
| **Sent Emails with Filters** | Outbox | `http://localhost:8000/outbox` |
| **Received Emails with Filters** | Inbound Emails | `http://localhost:8000/inbound-emails` |
| **Replied Emails** | Replied Emails | `http://localhost:8000/replied-emails` |
| **Template-Based Email & File Attachments** | RabbitMQ Test | `http://localhost:8000/rabbitmq-test` |

---

## 💡 **Demo Tips**

1. **Prepare Test Data:**
   - Have a valid tenant_id ready
   - Have some test emails in the system
   - Have at least one email template created

2. **Show Filters:**
   - Demonstrate tenant filtering (required)
   - Show date range filters
   - Show status filters
   - Show search functionality

3. **Show Pagination:**
   - Navigate through pages
   - Show per-page options
   - Show total count

4. **Template Demo:**
   - Select a template from dropdown
   - Show template variables
   - Enter sample template data
   - Send a test email

5. **File Attachment Demo:**
   - Show attachment URLs field
   - Enter a sample file URL
   - Explain the flow (URL → Email Service fetches → Attaches)

---

**Last Updated:** 2025-01-27

