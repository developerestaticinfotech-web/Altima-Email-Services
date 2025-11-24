# 📧 Replied Emails API - Simple Explanation

## 🤔 What Are "Replied Emails"?

**Replied emails** are emails that customers/clients send back to you **in response to emails you sent them**.

### Real-World Example:

1. **You send an email** to a customer: "Hello, how can I help you?"
2. **Customer replies**: "I need help with my order"
3. **That reply** is a "replied email"

---

## 🔍 How Does the System Know It's a Reply?

The system identifies replied emails using:

1. **`is_reply` flag** = `true` (marked as a reply)
2. **`in_reply_to` field** = Contains the message ID of the original email you sent
3. **`thread_id`** = Groups related emails in a conversation

### Example Flow:

```
Original Email (You Sent):
├── message_id: "msg-123"
├── subject: "Welcome to our service"
└── sent to: customer@example.com

Reply Email (Customer Sent Back):
├── message_id: "msg-456"
├── is_reply: true ✅
├── in_reply_to: "msg-123" (links to your original email)
├── subject: "Re: Welcome to our service"
└── from: customer@example.com
```

---

## 🎯 What Does the API Do?

The **Replied Emails API** lets you:

1. ✅ **Get all emails that are replies** to emails you sent
2. ✅ **Filter by date, sender, thread, etc.**
3. ✅ **See the full conversation thread**
4. ✅ **Link replies to the original outbound email**

---

## 📍 API Endpoint

**URL:** `GET /api/email/replies`

**Full URL:** `http://localhost:8000/api/email/replies`

---

## 🔧 How to Use the API

### **Required Parameter:**
- `tenant_id` - Your tenant ID (required)

### **Optional Filters:**
- `in_reply_to` - Get replies to a specific email message ID
- `thread_id` - Get all emails in a conversation thread
- `from_email` - Filter by sender email
- `date_from` - Filter from date (e.g., `2025-01-01`)
- `date_to` - Filter to date (e.g., `2025-01-31`)
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)

---

## 📝 Example API Calls

### **1. Get All Replied Emails (Basic)**

```bash
GET http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "email-uuid-1",
      "subject": "Re: Welcome to our service",
      "from_email": "customer@example.com",
      "from_name": "John Doe",
      "is_reply": true,
      "in_reply_to": "msg-123",
      "thread_id": "thread-abc",
      "received_at": "2025-01-15T10:30:00Z",
      "body_content": "Thank you for the welcome email...",
      "repliedToOutbound": {
        "id": "outbox-uuid",
        "subject": "Welcome to our service",
        "sent_at": "2025-01-15T09:00:00Z"
      },
      "thread_emails": [
        {
          "id": "email-uuid-1",
          "subject": "Re: Welcome to our service",
          "from_email": "customer@example.com",
          "received_at": "2025-01-15T10:30:00Z",
          "is_reply": true
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "from": 1,
    "to": 20
  }
}
```

---

### **2. Get Replies from Specific Date Range**

```bash
GET http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&date_from=2025-01-01&date_to=2025-01-31
```

---

### **3. Get Replies from Specific Sender**

```bash
GET http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&from_email=customer@example.com
```

---

### **4. Get Replies in a Specific Thread (Conversation)**

```bash
GET http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&thread_id=thread-abc
```

---

### **5. Get Replies to a Specific Email**

```bash
GET http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&in_reply_to=msg-123
```

---

### **6. With Pagination**

```bash
GET http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&page=2&per_page=10
```

---

## 💡 Use Cases (Why Your Client Needs This)

### **1. Customer Support Dashboard**
- Show all customer replies to support tickets
- Track response times
- Monitor customer satisfaction

### **2. Sales Follow-up**
- See which prospects replied to your sales emails
- Track engagement
- Identify hot leads

### **3. Email Campaign Analytics**
- Measure reply rates
- See which campaigns get responses
- Track conversation threads

### **4. CRM Integration**
- Sync replies to CRM system
- Link replies to original tickets/contacts
- Update customer records

---

## 🔗 How It Connects to Other Features

### **Relationship to Outbound Emails:**
- Replied emails are **linked** to the original outbound email via `in_reply_to`
- You can see the full conversation: **Outbound → Reply → Reply → Reply**

### **Relationship to Inbound Emails:**
- All replied emails are also **inbound emails**
- But not all inbound emails are replies
- The API filters only the ones marked as `is_reply = true`

---

## 🧪 Testing the API

### **Using Browser:**
```
http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2
```

### **Using cURL:**
```bash
curl -X GET "http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2" \
  -H "Accept: application/json"
```

### **Using Postman:**
1. Method: `GET`
2. URL: `http://localhost:8000/api/email/replies`
3. Query Params:
   - `tenant_id`: `01996243-2d8c-726d-a5c2-81b7005ce9a2`
   - `page`: `1`
   - `per_page`: `20`

---

## 📊 Response Structure

### **Success Response:**
```json
{
  "success": true,
  "data": [/* array of replied emails */],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "from": 1,
    "to": 20
  },
  "filters": {
    "date_from": "2025-01-01",
    "date_to": "2025-01-31"
  }
}
```

### **Error Response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "tenant_id": ["The tenant id field is required."]
  }
}
```

---

## 🔍 Where to Find the Code

- **Controller:** `email-microservice/app/Http/Controllers/Api/EmailController.php`
- **Method:** `getRepliedEmails()` (Line 1559)
- **Route:** `email-microservice/routes/api.php` (Line 35)
- **Model:** `email-microservice/app/Models/InboundEmail.php`

---

## ✅ Summary

**Replied Emails API** = Get all emails that customers sent back to you in response to emails you sent them.

**Key Features:**
- ✅ Filters only emails marked as replies (`is_reply = true`)
- ✅ Links replies to original outbound emails
- ✅ Shows full conversation threads
- ✅ Supports pagination and filtering
- ✅ Returns detailed email information

---

## 🆘 Need Help?

If you're not sure what the client wants, ask them:
1. "Do you want to see replies to specific emails or all replies?"
2. "Do you need filtering by date, sender, or thread?"
3. "Do you want this in a dashboard or just as an API?"

The API is already built and ready to use! 🎉

