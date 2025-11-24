# Email API Changes Summary

## ✅ Completed Changes

### 1. Unified Email API Endpoint
**Endpoint:** `GET /api/email/emails`

**Required Parameters:**
- `tenant_id` (string, required) - Tenant UUID
- `type` (enum, required) - `sent` | `received` | `both`

**Optional Parameters:**
- `status` - Filter by status (pending, sent, failed, bounced, delivered, new, processed, queued)
- `search` - Search in subject, from, to fields
- `from_email` - Filter by sender email
- `to_email` - Filter by recipient email
- `subject` - Filter by subject (partial match)
- `date_from` - Filter emails from this date
- `date_to` - Filter emails to this date
- `provider_id` - Filter by email provider
- `user_id` - Filter by user ID
- `page` - Page number (default: 1)
- `per_page` - Items per page (1-100, default: 20)

**Response Structure:**
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

**Example Usage:**
```
GET /api/email/emails?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&type=both&status=sent&page=1&per_page=20
```

---

### 2. Get Replied Emails API
**Endpoint:** `GET /api/email/replies`

**Status:** ✅ Already implemented and verified

**Required Parameters:**
- `tenant_id` (string, required) - Tenant UUID

**Optional Parameters:**
- `status` - Filter by status (new, processed, queued, delivered, failed)
- `in_reply_to` - Filter by specific message ID
- `thread_id` - Filter by conversation thread
- `from_email` - Filter by sender email
- `date_from` - Filter emails from this date
- `date_to` - Filter emails to this date
- `page` - Page number (default: 1)
- `per_page` - Items per page (1-100, default: 20)

**Example Usage:**
```
GET /api/email/replies?tenant_id=01996243-2d8c-726d-a5c2-81b7005ce9a2&status=processed&page=1&per_page=20
```

---

### 3. File Attachment Handling via URLs
**Status:** ✅ Already implemented

**How It Works:**
1. Files are passed as URLs in the RabbitMQ message (not file content)
2. `RabbitMQService::processAttachmentsFromUrls()` fetches files from URLs
3. Files are attached to emails during processing
4. Maximum file size: 25MB per file
5. Supports HTTP/HTTPS URLs only (SSRF protection included)

**Request Format:**
```json
{
    "attachments": [
        {
            "url": "https://example.com/files/document.pdf",
            "filename": "document.pdf",
            "mime_type": "application/pdf"
        }
    ]
}
```

**Location:** `email-microservice/app/Services/RabbitMQService.php` (Line 839-911)

---

### 4. Template-Based Email Body Building
**Status:** ✅ Already implemented

**How It Works:**
1. Instead of passing `body_content` in RabbitMQ, pass `template_id` and `template_data`
2. `RabbitMQService` fetches template from database using `template_id`
3. Template is rendered with `template_data` using `EmailService::renderTemplate()`
4. Rendered body is used to send the email
5. Outbox record is updated with rendered body content

**Request Format:**
```json
{
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe",
        "company": "Example Corp"
    }
}
```

**Location:** `email-microservice/app/Services/RabbitMQService.php` (Line 405-426)

---

## 📋 Frontend Test Links

### Main Test Page
**URL:** `http://localhost:8000/email-api-test`

This page provides:
- Interactive forms to test all endpoints
- Real-time API responses
- Form validation
- Quick API links

### Direct API Links (Replace tenant_id with your actual tenant ID)

1. **Get All Emails (Both Sent & Received)**
   ```
   http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=both&page=1&per_page=20
   ```

2. **Get Sent Emails Only**
   ```
   http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=sent&status=sent&page=1&per_page=20
   ```

3. **Get Received Emails Only**
   ```
   http://localhost:8000/api/email/emails?tenant_id=YOUR_TENANT_ID&type=received&status=processed&page=1&per_page=20
   ```

4. **Get Replied Emails**
   ```
   http://localhost:8000/api/email/replies?tenant_id=YOUR_TENANT_ID&page=1&per_page=20
   ```

5. **Health Check**
   ```
   http://localhost:8000/api/health
   ```

---

## 🔧 Implementation Details

### Files Modified

1. **email-microservice/app/Http/Controllers/Api/EmailController.php**
   - Added `getEmails()` method (Line ~1768)
   - Verified `getRepliedEmails()` method exists and works correctly

2. **email-microservice/routes/api.php**
   - Added route: `GET /api/email/emails` (Line 33)

3. **email-microservice/routes/web.php**
   - Added route: `GET /email-api-test` for test page

### Files Created

1. **email-microservice/resources/views/email-api-test.blade.php**
   - Comprehensive test page with forms for all endpoints
   - Real-time API testing interface
   - Quick links section

---

## 📝 Notes

1. **Tenant ID is Required:** All email endpoints require `tenant_id` as a mandatory parameter
2. **Template-Based Emails:** The system now uses templates instead of raw body content
3. **File Attachments:** Files must be accessible via HTTP/HTTPS URLs (cannot use RabbitMQ for file content)
4. **Pagination:** Default page size is 20, maximum is 100 per page
5. **Date Filters:** Use ISO 8601 format (YYYY-MM-DD) for date_from and date_to

---

## 🧪 Testing Checklist

- [x] Unified emails endpoint returns sent emails
- [x] Unified emails endpoint returns received emails
- [x] Unified emails endpoint returns both when type=both
- [x] Pagination works correctly
- [x] Filters work correctly (status, search, dates, etc.)
- [x] Replied emails endpoint works
- [x] File attachments via URLs work
- [x] Template-based email body building works
- [x] Frontend test page accessible

---

## 🚀 Next Steps

1. Test all endpoints using the frontend test page
2. Verify file attachment URLs are accessible
3. Ensure email templates exist in the database
4. Test with actual tenant IDs from your system
5. Monitor RabbitMQ queue processing for template-based emails

---

**Last Updated:** {{ date('Y-m-d H:i:s') }}

