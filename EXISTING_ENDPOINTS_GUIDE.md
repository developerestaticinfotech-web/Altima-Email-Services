# Existing Email Endpoints Guide

## 📋 Overview

You already have email listing endpoints! Here's how to use them:

---

## ✅ Existing Endpoints

### **1. Get Sent Emails (Outbox)**

**URL:** `GET /api/outbox/emails`

**Controller:** `OutboxController::getEmails()`

**Path:** `email-microservice/app/Http/Controllers/OutboxController.php` (Line 25)

**Required Parameters:**
- `tenant_id` (string, required) - Tenant UUID

**Optional Parameters:**
- `status` - Filter by status: `pending`, `sent`, `failed`, `bounced`, `delivered`
- `search` - Search in subject, from, to fields
- `from_email` - Filter by sender email
- `to_email` - Filter by recipient email
- `subject` - Filter by subject (partial match)
- `date_from` - Filter emails from this date
- `date_to` - Filter emails to this date
- `provider_id` - Filter by email provider
- `user_id` - Filter by user ID
- `page` - Page number (default: 1)
- `per_page` - Items per page (1-100, default: 15)

**Example:**
```
http://localhost:8000/api/outbox/emails?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&status=sent&page=1&per_page=20
```

---

### **2. Get Received Emails (Inbound)**

**URL:** `GET /api/email/inbound`

**Controller:** `InboundEmailController::getInboundEmails()`

**Path:** `email-microservice/app/Http/Controllers/Api/InboundEmailController.php` (Line 28)

**Required Parameters:**
- `tenant_id` (string, required) - Tenant UUID

**Optional Parameters:**
- `status` - Filter by status: `new`, `processed`, `queued`, `delivered`, `failed`
- `is_reply` - Filter replies (boolean)
- `thread_id` - Filter by conversation thread
- `from_email` - Filter by sender email
- `date_from` - Filter emails from this date
- `date_to` - Filter emails to this date
- `page` - Page number (default: 1)
- `per_page` - Items per page (1-100, default: 20)

**Example:**
```
http://localhost:8000/api/email/inbound?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&status=processed&page=1&per_page=20
```

---

### **3. Get Replied Emails (NEW)**

**URL:** `GET /api/email/replies`

**Controller:** `EmailController::getRepliedEmails()`

**Path:** `email-microservice/app/Http/Controllers/Api/EmailController.php` (Line 1740)

**Required Parameters:**
- `tenant_id` (string, required) - Tenant UUID

**Optional Parameters:**
- `in_reply_to` - Get replies to specific message ID
- `thread_id` - Get all emails in a conversation thread
- `from_email` - Filter by sender email
- `date_from` - Filter replies from this date
- `date_to` - Filter replies to this date
- `page` - Page number (default: 1)
- `per_page` - Items per page (1-100, default: 20)

**Example:**
```
http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&page=1&per_page=20
```

---

## 🔄 Summary

| Endpoint | Purpose | Controller |
|----------|---------|------------|
| `GET /api/outbox/emails` | Get sent emails | `OutboxController` |
| `GET /api/email/inbound` | Get received emails | `InboundEmailController` |
| `GET /api/email/replies` | Get replied emails | `EmailController` |

---

## 📝 Notes

- **Tenant ID is required** for all endpoints
- Both existing endpoints have been **enhanced** with additional filters
- The **replied emails endpoint** is new functionality
- All endpoints support pagination and filtering

---

## 🧪 Quick Test URLs

**Your Tenant ID:** `01996243-2d8c-726d-a5c2-81b7005ce9a2`

### **Get Sent Emails:**
```
http://localhost:8000/api/outbox/emails?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&status=sent&page=1&per_page=20
```

### **Get Received Emails:**
```
http://localhost:8000/api/email/inbound?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&page=1&per_page=20
```

### **Get Replied Emails:**
```
http://localhost:8000/api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&page=1&per_page=20
```

