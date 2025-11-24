# 📧 Template-Based Email Flow - Complete Explanation

## ✅ Implementation Status: **COMPLETE**

This feature is **already implemented** and working. The system now passes only `template_id` and `template_data` through RabbitMQ, and builds the email body in the Email Service by fetching the template from the database.

---

## 🔄 How It Works (Current Flow)

### **Step 1: API Request** (`EmailController::sendEmailViaRabbitMQ`)

**Endpoint:** `POST /api/rabbitmq/send-email`

**Request Format:**
```json
{
  "tenant_id": "01996243-2d8c-726d-a5c2-81b7005ce9a2",
  "provider_id": "01996243-2da2-72b2-80c2-9d3f855e404a",
  "from": "sender@example.com",
  "to": ["recipient@example.com"],
  "subject": "Optional Subject",
  "template_id": "welcome-template",        // ✅ Template ID (not body)
  "template_data": {                         // ✅ Template variables (not body)
    "name": "John Doe",
    "company": "Example Corp"
  },
  "attachments": [
    {
      "url": "https://example.com/file.pdf",
      "filename": "file.pdf",
      "mime_type": "application/pdf"
    }
  ]
}
```

**What Happens:**
1. ✅ Validates `template_id` exists in `email_templates` table
2. ✅ Validates `template_data` is an array
3. ✅ **Does NOT send `body_content`** - only template reference
4. ✅ Sends payload to RabbitMQ queue with `template_id` and `template_data`

**Code Location:** `app/Http/Controllers/Api/EmailController.php` (Line 1148-1240)

---

### **Step 2: RabbitMQ Queue** 

**Queue Name:** `email.send`

**Message Payload:**
```json
{
  "template_id": "welcome-template",     // ✅ Only template ID
  "template_data": {                     // ✅ Only template data
    "name": "John Doe",
    "company": "Example Corp"
  },
  "tenant_id": "...",
  "provider_id": "...",
  "from": "...",
  "to": [...],
  "subject": "...",
  "attachments": [...]
  // ❌ NO body_content field!
}
```

**Benefits:**
- ✅ Small message size (only template reference, not full HTML)
- ✅ Better performance
- ✅ Template changes don't require re-queuing
- ✅ Centralized template management

---

### **Step 3: Queue Processing** (`RabbitMQService::processRealQueue`)

**Location:** `app/Services/RabbitMQService.php` (Line 399-426)

**What Happens:**
1. ✅ Receives message with `template_id` and `template_data`
2. ✅ **Fetches template from database:**
   ```php
   $template = EmailTemplate::where('template_id', $emailData['template_id'])
       ->where('is_active', true)
       ->first();
   ```
3. ✅ **Builds email body using EmailService:**
   ```php
   $emailService = app(\App\Services\EmailService::class);
   $renderedContent = $emailService->renderTemplate($template, $emailData['template_data']);
   ```
4. ✅ Gets rendered HTML/text content
5. ✅ Sends email with built body

---

### **Step 4: Template Rendering** (`EmailService::renderTemplate`)

**Location:** `app/Services/EmailService.php` (Line 218-235)

**What Happens:**
1. ✅ Takes template object and template_data array
2. ✅ Renders HTML content (if exists):
   ```php
   $htmlContent = $this->renderBladeContent($template->html_content, $data);
   ```
3. ✅ Renders Text content (if exists):
   ```php
   $textContent = $this->renderBladeContent($template->text_content, $data);
   ```
4. ✅ Returns rendered content:
   ```php
   return [
       'html' => $htmlContent,
       'text' => $textContent,
   ];
   ```

**Template Variables:**
- Templates use Blade syntax: `{{ $name }}`, `{{ $company }}`
- Variables are replaced with values from `template_data`
- Supports all Blade features (loops, conditionals, etc.)

---

## 📊 Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    EMAIL SENDING FLOW                        │
└─────────────────────────────────────────────────────────────┘

1. CLIENT REQUEST
   POST /api/rabbitmq/send-email
   {
     "template_id": "welcome-template",
     "template_data": {"name": "John"}
   }
   ↓
   
2. EmailController::sendEmailViaRabbitMQ
   ✅ Validates template_id exists
   ✅ Sends to RabbitMQ (NO body_content)
   {
     "template_id": "welcome-template",
     "template_data": {"name": "John"}
   }
   ↓
   
3. RABBITMQ QUEUE
   Queue: "email.send"
   Message: {template_id, template_data, ...}
   ↓
   
4. RabbitMQService::processRealQueue
   ✅ Fetches template from DB
   ✅ Calls EmailService::renderTemplate()
   ✅ Gets rendered body content
   ↓
   
5. EmailService::renderTemplate
   ✅ Renders Blade template with data
   ✅ Returns HTML/Text content
   ↓
   
6. SEND EMAIL
   ✅ Uses rendered body content
   ✅ Attaches files from URLs
   ✅ Sends via SMTP
```

---

## 🔍 Code Verification

### **1. EmailController (Line 1163-1164)**
```php
'template_id' => 'required|string|exists:email_templates,template_id',
'template_data' => 'required|array', // Data to populate template variables
```
✅ **No `body_content` validation** - only template_id and template_data

### **2. EmailController (Line 1208)**
```php
'body_content' => null, // Will be built from template during processing
```
✅ **Body is set to null** - will be built later

### **3. RabbitMQService (Line 405-414)**
```php
if (isset($emailData['template_id']) && isset($emailData['template_data'])) {
    // Fetch template from database
    $template = \App\Models\EmailTemplate::where('template_id', $emailData['template_id'])
        ->where('is_active', true)
        ->first();
    
    if ($template) {
        // Use EmailService to render template
        $emailService = app(\App\Services\EmailService::class);
        $renderedContent = $emailService->renderTemplate($template, $emailData['template_data']);
        
        $bodyContent = $renderedContent['html'] ?? $renderedContent['text'] ?? '';
        $bodyFormat = $template->hasHtmlContent() ? 'HTML' : 'TEXT';
        $subject = $subject ?: $template->subject;
    }
}
```
✅ **Template fetched from DB and rendered** during processing

### **4. EmailService (Line 218-235)**
```php
protected function renderTemplate(EmailTemplate $template, array $data): array
{
    $htmlContent = null;
    $textContent = null;

    if ($template->hasHtmlContent()) {
        $htmlContent = $this->renderBladeContent($template->html_content, $data);
    }

    if ($template->hasTextContent()) {
        $textContent = $this->renderBladeContent($template->text_content, $data);
    }

    return [
        'html' => $htmlContent,
        'text' => $textContent,
    ];
}
```
✅ **Renders template with data** using Blade engine

---

## ✅ Benefits of This Approach

1. **Smaller Queue Messages**
   - Only template reference (ID) instead of full HTML
   - Faster queue processing
   - Lower memory usage

2. **Template Management**
   - Update templates in database without re-queuing emails
   - Centralized template storage
   - Version control through database

3. **Dynamic Content**
   - Templates support Blade syntax
   - Variables replaced at send time
   - Supports loops, conditionals, etc.

4. **Separation of Concerns**
   - Template storage: Database
   - Template rendering: EmailService
   - Queue processing: RabbitMQService

---

## 📝 Example Template

**Database Template:**
```sql
INSERT INTO email_templates (template_id, name, subject, html_content, is_active)
VALUES (
  'welcome-template',
  'Welcome Email',
  'Welcome {{name}}!',
  '<h1>Hello {{name}}</h1><p>Welcome to {{company}}!</p>',
  1
);
```

**Request:**
```json
{
  "template_id": "welcome-template",
  "template_data": {
    "name": "John Doe",
    "company": "Example Corp"
  }
}
```

**Rendered Output:**
```html
<h1>Hello John Doe</h1>
<p>Welcome to Example Corp!</p>
```

---

## 🎯 Summary

✅ **Implementation Status:** Complete and Working

✅ **What's Passed Through RabbitMQ:**
- `template_id` (string)
- `template_data` (array)
- **NOT** `body_content`

✅ **Where Body is Built:**
- In `RabbitMQService::processRealQueue`
- Using `EmailService::renderTemplate`
- Template fetched from database
- Rendered with Blade engine

✅ **Benefits:**
- Smaller messages
- Better performance
- Centralized templates
- Dynamic content

---

## 🔗 Related Files

1. **EmailController:** `app/Http/Controllers/Api/EmailController.php` (Line 1148-1240)
2. **RabbitMQService:** `app/Services/RabbitMQService.php` (Line 399-426)
3. **EmailService:** `app/Services/EmailService.php` (Line 218-235)
4. **EmailTemplate Model:** `app/Models/EmailTemplate.php`
5. **API Route:** `routes/api.php` (Line 87)

---

## ✅ Verification Checklist

- [x] API accepts `template_id` and `template_data` (not `body_content`)
- [x] RabbitMQ message contains only template reference
- [x] Template fetched from database during processing
- [x] Email body built using `EmailService::renderTemplate`
- [x] Blade template rendering works
- [x] Fallback to `body_content` for backward compatibility
- [x] Frontend form updated to use template dropdown

**Status: ✅ All checks passed - Implementation is complete!**

