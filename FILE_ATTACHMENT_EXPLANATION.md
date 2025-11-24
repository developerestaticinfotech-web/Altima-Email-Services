# 📎 File Attachment Handling - Complete Explanation

## 🤔 What the Client is Asking

**Question:** "How do we pass the files (We cannot use RabbitMQ for this)"

**Translation:** 
- You cannot send large file content directly through RabbitMQ messages (size limits, performance issues)
- Instead, you need to pass **file URLs** in the RabbitMQ message
- The Email Service should **fetch files from URLs** and attach them when sending emails

---

## 🎯 Current Implementation Status

### ✅ What's Already Working

1. **API Accepts Attachment URLs** (`EmailController::sendEmailViaRabbitMQ`)
   - Validation expects: `attachments.*.url`, `attachments.*.filename`, `attachments.*.mime_type`
   - Location: `email-microservice/app/Http/Controllers/Api/EmailController.php` (Line 1165-1168)

2. **Outbox Record Stores URLs**
   - Attachments are stored as URLs in the outbox record
   - Location: `email-microservice/app/Http/Controllers/Api/EmailController.php` (Line 1210)

### ❌ What's Missing

1. **RabbitMQ Processing Doesn't Fetch Files**
   - `RabbitMQService::processRealQueue()` receives attachment URLs but doesn't fetch them
   - Files are not attached to the actual email when sending
   - Location: `email-microservice/app/Services/RabbitMQService.php` (Line 432-446)

2. **Email Sending Without Attachments**
   - The `Mail::html()` and `Mail::raw()` calls don't include attachments
   - Location: `email-microservice/app/Services/RabbitMQService.php` (Line 433-445)

---

## 🔄 How It Should Work

### **Step-by-Step Flow:**

```
1. Client sends email request with attachment URLs
   ↓
   POST /api/email/send-email
   {
     "attachments": [
       {
         "url": "https://example.com/files/document.pdf",
         "filename": "document.pdf",
         "mime_type": "application/pdf"
       }
     ]
   }
   ↓
2. EmailController validates and sends to RabbitMQ
   - Only URLs are sent (not file content)
   ↓
3. RabbitMQ stores message with URLs
   ↓
4. RabbitMQService processes queue
   - Fetches files from URLs
   - Downloads file content
   - Attaches to email
   - Sends email with attachments
```

---

## 📝 Implementation Details

### **Current Code Structure:**

**1. EmailController (Line 1165-1210)**
```php
'attachments' => 'nullable|array',
'attachments.*.url' => 'required_with:attachments|url', // ✅ Expects URLs
'attachments.*.filename' => 'required_with:attachments|string',
'attachments.*.mime_type' => 'required_with:attachments|string',
```

**2. RabbitMQService (Line 432-446) - MISSING ATTACHMENT HANDLING**
```php
Mail::html($bodyContent, function ($message) use ($recipients, $subject, $fromEmail, $fromName) {
    $message->to($recipients)->subject($subject);
    // ❌ No attachment handling here!
});
```

---

## ✅ Solution: What Needs to Be Fixed

### **1. Add URL Fetching Logic**

Create a method to fetch files from URLs:
```php
protected function fetchAttachmentFromUrl(string $url): string
{
    // Fetch file content from URL
    // Handle errors (404, timeout, etc.)
    // Return file content
}
```

### **2. Update Email Sending to Include Attachments**

Modify `RabbitMQService::processRealQueue()` to:
- Loop through attachment URLs
- Fetch each file
- Attach to email using Laravel's `$message->attach()`

---

## 🎯 Why This Approach?

### **Benefits:**
1. ✅ **No Size Limits** - RabbitMQ messages stay small (only URLs)
2. ✅ **Better Performance** - No large payloads in queue
3. ✅ **Scalability** - Can handle large files
4. ✅ **Flexibility** - Files can be stored anywhere (S3, CDN, etc.)

### **Trade-offs:**
1. ⚠️ **URL Must Be Accessible** - Files must be publicly accessible or use signed URLs
2. ⚠️ **Network Dependency** - Email service must be able to reach file URLs
3. ⚠️ **Error Handling** - Need to handle URL fetch failures gracefully

---

## 📍 Related Code Locations

1. **EmailController** - `app/Http/Controllers/Api/EmailController.php`
   - Line 1165-1168: Validation rules
   - Line 1210: Storing attachments in outbox

2. **RabbitMQService** - `app/Services/RabbitMQService.php`
   - Line 399-426: Template processing
   - Line 432-446: Email sending (NEEDS ATTACHMENT HANDLING)

3. **EmailProcessingService** - `app/Services/EmailProcessingService.php`
   - Line 319-391: Attachment storage logic
   - Line 376-391: `getAttachmentContent()` method (supports URLs)

---

## 🔧 Next Steps

1. ✅ **Implement URL fetching** in `RabbitMQService`
2. ✅ **Add attachment handling** to email sending
3. ✅ **Add error handling** for failed URL fetches
4. ✅ **Update documentation** with examples

---

## 📚 Example Request Format

```json
{
  "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
  "provider_id": "provider-123",
  "from": "sender@example.com",
  "to": ["recipient@example.com"],
  "subject": "Email with Attachment",
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

---

## ⚠️ Important Notes

1. **File URLs must be publicly accessible** or use signed/temporary URLs
2. **File size limits** should be considered (recommend max 25MB per file)
3. **Timeout handling** for slow URL fetches
4. **Retry logic** for failed fetches
5. **Security** - Validate URLs to prevent SSRF attacks

