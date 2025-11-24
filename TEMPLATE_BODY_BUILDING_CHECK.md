# 📍 Where to Check Template-Based Email Body Building

## ✅ **Implementation Locations**

---

## 1️⃣ **API Endpoint - Where You Send the Request**

### **File:** `email-microservice/app/Http/Controllers/Api/EmailController.php`
### **Method:** `sendEmailViaRabbitMQ()`
### **Lines:** 1236-1328

**What to Check:**
- ✅ Validates `template_id` is required (Line 1251)
- ✅ Validates `template_data` is required (Line 1252)
- ✅ **Does NOT require `body_content`** - only template reference
- ✅ Sends to RabbitMQ with `template_id` and `template_data` (Line 1278)
- ✅ Creates Outbox record with `body_content = null` (Line 1296)

**Key Code:**
```php
// Line 1251-1252
'template_id' => 'required|string|exists:email_templates,template_id',
'template_data' => 'required|array', // Data to populate template variables

// Line 1296
'body_content' => null, // Will be built from template during processing
```

**API Endpoint to Test:**
```
POST http://localhost:8000/api/rabbitmq/send-email
```

**Test Request:**
```json
{
    "tenant_id": "YOUR_TENANT_ID",
    "provider_id": "YOUR_PROVIDER_ID",
    "from": "sender@example.com",
    "to": ["recipient@example.com"],
    "template_id": "welcome-template",
    "template_data": {
        "name": "John Doe",
        "company": "Example Corp"
    }
}
```

---

## 2️⃣ **RabbitMQ Processing - Where Template is Fetched from DB**

### **File:** `email-microservice/app/Services/RabbitMQService.php`
### **Method:** `processRealQueue()`
### **Lines:** 400-426

**What to Check:**
- ✅ Checks if `template_id` and `template_data` exist (Line 405)
- ✅ **Fetches template from database** (Lines 407-409)
- ✅ **Renders template using EmailService** (Line 414)
- ✅ **Builds body_content from rendered template** (Line 416)
- ✅ Uses template subject if not provided (Line 418)

**Key Code:**
```php
// Line 405-421
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
    } else {
        throw new \Exception("Template '{$emailData['template_id']}' not found or inactive");
    }
}
```

**What Happens:**
1. ✅ Template is fetched from `email_templates` table
2. ✅ Template is rendered with `template_data`
3. ✅ Body content is built from rendered template
4. ✅ Email is sent with built body

---

## 3️⃣ **Template Rendering - Where Body is Built**

### **File:** `email-microservice/app/Services/EmailService.php`
### **Method:** `renderTemplate()`
### **Lines:** 219-236

**What to Check:**
- ✅ Takes template object and template_data array
- ✅ Renders HTML content if exists (Line 225)
- ✅ Renders Text content if exists (Line 229)
- ✅ Returns rendered content array (Lines 232-235)

**Key Code:**
```php
// Line 219-236
public function renderTemplate(EmailTemplate $template, array $data): array
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

**Blade Rendering:**
- **File:** `email-microservice/app/Services/EmailService.php`
- **Method:** `renderBladeContent()`
- **Lines:** 241-253

Uses Laravel Blade engine to render template variables.

---

## 4️⃣ **Database - Where Templates are Stored**

### **Table:** `email_templates`
### **Model:** `email-microservice/app/Models/EmailTemplate.php`

**What to Check:**
- ✅ Templates stored in `email_templates` table
- ✅ Fields: `template_id`, `html_content`, `text_content`, `subject`, `variables`
- ✅ Template must be `is_active = true` to be used

**Check Template Exists:**
```sql
SELECT * FROM email_templates WHERE template_id = 'welcome-template' AND is_active = 1;
```

**API to Check Templates:**
```
GET http://localhost:8000/api/email/templates
GET http://localhost:8000/api/email/templates/{templateId}
```

---

## 5️⃣ **Outbox Record - Where Body is Updated**

### **File:** `email-microservice/app/Services/RabbitMQService.php`
### **Lines:** 478-500

**What to Check:**
- ✅ Outbox record created with `body_content = null` initially (Line 1296 in EmailController)
- ✅ **Outbox updated with rendered body_content** after processing (Line 486)

**Key Code:**
```php
// Line 478-500
$outboxRecord->update([
    'body_content' => $bodyContent, // Update with rendered content
    'body_format' => $bodyFormat,
    'status' => 'sent',
    'sent_at' => now(),
    // ...
]);
```

**Check in Database:**
```sql
SELECT id, message_id, template_id, body_content, body_format, status 
FROM outbox 
WHERE template_id = 'welcome-template' 
ORDER BY created_at DESC 
LIMIT 10;
```

---

## 🧪 **How to Test & Verify**

### **Step 1: Send Email with Template**

**Request:**
```bash
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
    }
}
```

### **Step 2: Check RabbitMQ Queue**

**Check Logs:**
```bash
# Check Laravel logs
tail -f email-microservice/storage/logs/laravel.log
```

**Look for:**
- ✅ "Processing message from queue email.send"
- ✅ "Fetch template from database"
- ✅ "Template rendered successfully"

### **Step 3: Check Database**

**Check Outbox:**
```sql
SELECT 
    id,
    message_id,
    template_id,
    body_content,  -- Should be populated after processing
    body_format,
    status,
    created_at,
    sent_at
FROM outbox
WHERE template_id = 'welcome-template'
ORDER BY created_at DESC
LIMIT 1;
```

**Expected:**
- ✅ `body_content` should contain rendered HTML/text
- ✅ `body_format` should be 'HTML' or 'TEXT'
- ✅ `status` should be 'sent' after processing

### **Step 4: Verify Email Sent**

**Check Email:**
- ✅ Email should be sent with rendered body
- ✅ Template variables should be replaced with actual values
- ✅ Subject should match template subject (if not overridden)

---

## 📋 **Checklist - Verify Implementation**

- [ ] **API accepts `template_id` and `template_data`** (EmailController.php Line 1251-1252)
- [ ] **API does NOT require `body_content`** (EmailController.php Line 1251-1252)
- [ ] **Outbox created with `body_content = null`** (EmailController.php Line 1296)
- [ ] **Template fetched from database** (RabbitMQService.php Line 407-409)
- [ ] **Template rendered with template_data** (RabbitMQService.php Line 414)
- [ ] **Body built from rendered template** (RabbitMQService.php Line 416)
- [ ] **Outbox updated with rendered body** (RabbitMQService.php Line 486)
- [ ] **Email sent with built body** (RabbitMQService.php Line 436-470)

---

## 🔍 **Debugging - What to Look For**

### **In Logs:**
```
[INFO] Processing message from queue email.send
[INFO] Fetch template from database: welcome-template
[INFO] Template rendered successfully
[INFO] Email sent successfully
```

### **In Database:**
- **Before Processing:** `body_content = NULL` in outbox
- **After Processing:** `body_content = "<html>...</html>"` in outbox

### **In Code:**
- **Line 405:** Check if template_id exists in message
- **Line 407-409:** Template fetch from DB
- **Line 414:** Template rendering
- **Line 416:** Body content assignment
- **Line 486:** Outbox update with rendered body

---

## 📝 **Summary**

**Flow:**
1. **API Request** → Send `template_id` + `template_data` (NO `body_content`)
2. **RabbitMQ Queue** → Receives template reference
3. **Queue Processing** → Fetches template from DB (Line 407-409)
4. **Template Rendering** → Builds body from template (Line 414-416)
5. **Email Sending** → Sends email with built body (Line 436-470)
6. **Outbox Update** → Updates with rendered body (Line 486)

**Key Files:**
- `app/Http/Controllers/Api/EmailController.php` (Line 1236-1328)
- `app/Services/RabbitMQService.php` (Line 400-426)
- `app/Services/EmailService.php` (Line 219-236)

---

**Last Updated:** 2025-11-24

