# 🔍 Debugging Queue Email Failures

## ✅ Current Status
- **Queue Processing:** ✅ Working
- **Processed:** 2 messages
- **Success:** 0 emails
- **Failed:** 2 emails

The queue is processing, but emails are failing. Let's find out why.

---

## 📍 Step 1: Check Failed Emails in Outbox

### **View Failed Emails:**
1. Go to: `http://localhost:8000/outbox`
2. Select your tenant from the dropdown
3. In **Status filter**, select **"failed"**
4. Click **"Apply Filters"**
5. Click **"View Details"** on any failed email
6. Check the **error_message** field for the exact error

---

## 📋 Step 2: Check Laravel Logs

### **View Recent Errors:**
```bash
cd email-microservice
Get-Content storage/logs/laravel.log -Tail 50 | Select-String -Pattern "Error|Failed|Exception" -Context 2
```

### **Look for:**
- `Error processing message from queue`
- `Failed to send email via queue processing`
- `Template '...' not found`
- `No active email provider found`
- `SMTP` errors
- `Swift_TransportException`

---

## 🔍 Step 3: Common Failure Reasons

### **1. Template Not Found**
**Error:** `Template 'welcome-template' not found or inactive`

**Solution:**
- Check if template exists: `http://localhost:8000/api/email/templates`
- Verify template is active (`is_active = true`)
- Check template_id in queue message matches database

### **2. No Active Email Provider**
**Error:** `No active email provider found`

**Solution:**
- Go to: `http://localhost:8000/providers`
- Ensure you have an active provider
- Check provider is enabled (`is_active = true`)

### **3. SMTP Authentication Failed**
**Error:** `535-5.7.8 Username and Password not accepted`

**Solution:**
- Use Gmail App Password (not regular password)
- Verify SMTP credentials in provider config
- Check 2-Step Verification is enabled

### **4. Template Data Missing**
**Error:** `Template rendering failed` or `Undefined variable`

**Solution:**
- Check `template_data` in queue message
- Ensure all required template variables are provided
- Verify template_data is valid JSON

### **5. Invalid Email Addresses**
**Error:** `Invalid email address` or `Address in mailbox given [] does not comply with RFC`

**Solution:**
- Verify `to`, `from`, `cc`, `bcc` email addresses are valid
- Check email format (must be valid email addresses)

---

## 🛠️ Step 4: Check Queue Message Content

The queue messages might have invalid data. Check what's in the queue:

1. **Via RabbitMQ Management:**
   - Go to: `http://localhost:15672`
   - Login: `guest` / `guest`
   - Navigate to **Queues** → `email.send`
   - Click on a message to see its content
   - Check for:
     - Valid `template_id`
     - Valid `template_data`
     - Valid email addresses
     - Valid `tenant_id` and `provider_id`

2. **Via Laravel Logs:**
   - Look for: `Processing message from queue`
   - Check the logged message content
   - Verify all required fields are present

---

## 📊 Step 5: Check Database Records

### **Check Outbox Table:**
```sql
SELECT id, subject, from, to, status, error_message, template_id, created_at 
FROM outbox 
WHERE status = 'failed' 
ORDER BY created_at DESC 
LIMIT 10;
```

### **Check Email Templates:**
```sql
SELECT template_id, name, is_active 
FROM email_templates 
WHERE is_active = true;
```

### **Check Email Providers:**
```sql
SELECT provider_id, provider_name, is_active, tenant_id 
FROM email_providers 
WHERE is_active = true;
```

---

## 🎯 Quick Debugging Checklist

- [ ] Check failed emails in outbox (`http://localhost:8000/outbox` with "failed" filter)
- [ ] Read error_message field in failed emails
- [ ] Check Laravel logs for detailed errors
- [ ] Verify email provider is active
- [ ] Verify template exists and is active
- [ ] Check template_data has all required variables
- [ ] Verify SMTP credentials are correct
- [ ] Check email addresses are valid
- [ ] Verify tenant_id and provider_id are valid

---

## 🔧 Step 6: Fix Common Issues

### **Issue: Template Not Found**
```sql
-- Check if template exists
SELECT * FROM email_templates WHERE template_id = 'your-template-id';

-- If missing, create it or use existing template_id
```

### **Issue: Provider Not Found**
```sql
-- Check if provider exists and is active
SELECT * FROM email_providers WHERE provider_id = 'your-provider-id' AND is_active = true;

-- If missing, create provider or activate existing one
```

### **Issue: SMTP Errors**
- Update provider config with correct SMTP settings
- Use Gmail App Password for Gmail
- Verify SMTP host, port, encryption match

---

## 📝 Step 7: Re-process After Fixing

1. Fix the issues found above
2. Re-process the queue:
   - Go to: `http://localhost:8000/rabbitmq-test`
   - Click **"⚡ Process Queue"**
3. Check results:
   - Should show `success > 0` if fixed
   - Check outbox for sent emails

---

## 💡 Next Steps

1. **Check Failed Emails First:**
   - `http://localhost:8000/outbox` → Filter by "failed"
   - View error messages

2. **Check Logs:**
   - `storage/logs/laravel.log`
   - Look for recent errors

3. **Fix Issues:**
   - Based on error messages
   - Update provider/template/config

4. **Re-test:**
   - Process queue again
   - Verify emails send successfully

---

**The most important step is to check the failed emails in the outbox - they will show you the exact error message!**

