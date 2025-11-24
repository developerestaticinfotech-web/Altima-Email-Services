# 🔧 Gmail SMTP Troubleshooting Guide

## ❌ Problem: API Success but Email Not Received

If the API returns success but you're not receiving emails, here are the most common causes and solutions:

---

## 🔍 **Step 1: Check Laravel Logs**

Check for actual SMTP errors in the logs:

```bash
cd email-microservice
tail -f storage/logs/laravel.log | grep -i "smtp\|mail\|error"
```

Look for:
- `Swift_TransportException`
- `Connection could not be established`
- `Authentication failed`
- `535-5.7.8` (Gmail authentication error)

---

## 🔐 **Step 2: Verify Gmail App Password**

Gmail requires an **App Password** (not your regular password) for SMTP.

### **How to Generate Gmail App Password:**

1. Go to: https://myaccount.google.com/
2. Click **Security** (left sidebar)
3. Under **"How you sign in to Google"**, click **2-Step Verification**
   - Enable 2-Step Verification if not already enabled
4. Scroll down and click **App passwords**
5. Select app: **Mail**
6. Select device: **Other (Custom name)**
7. Enter name: **"Email Microservice"**
8. Click **Generate**
9. Copy the 16-character password (spaces don't matter)
10. Use this password in your provider config (not your regular Gmail password)

### **Your Provider Config Should Have:**
```json
{
  "smtp_host": "smtp.gmail.com",
  "smtp_port": 587,
  "smtp_username": "your-email@gmail.com",
  "smtp_password": "xxxx xxxx xxxx xxxx",  // ← App Password (16 chars)
  "smtp_encryption": "tls"
}
```

---

## 🔥 **Step 3: Check Firewall/Network**

### **Windows Firewall:**
1. Open **Windows Defender Firewall**
2. Check if port **587** (SMTP) is blocked
3. Allow Laravel/PHP through firewall if needed

### **Test SMTP Connection:**
```bash
# Test if you can connect to Gmail SMTP
telnet smtp.gmail.com 587
```

If connection fails, firewall or network is blocking it.

---

## 📧 **Step 4: Verify SMTP Settings**

### **Correct Gmail SMTP Settings:**
- **Host:** `smtp.gmail.com`
- **Port:** `587` (TLS) or `465` (SSL)
- **Encryption:** `tls` (for port 587) or `ssl` (for port 465)
- **Username:** Your full Gmail address
- **Password:** App Password (16 characters)

### **Common Mistakes:**
❌ Using regular Gmail password instead of App Password
❌ Wrong port (using 25 instead of 587)
❌ Wrong encryption (using `ssl` with port 587)
❌ Missing `@gmail.com` in username

---

## 🧪 **Step 5: Test SMTP Connection Directly**

Create a test script to verify SMTP connection:

```php
// test-smtp.php
<?php
require 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

config([
    'mail.default' => 'smtp',
    'mail.mailers.smtp.transport' => 'smtp',
    'mail.mailers.smtp.host' => 'smtp.gmail.com',
    'mail.mailers.smtp.port' => 587,
    'mail.mailers.smtp.username' => 'your-email@gmail.com',
    'mail.mailers.smtp.password' => 'your-app-password',
    'mail.mailers.smtp.encryption' => 'tls',
]);

try {
    Mail::raw('Test email', function ($message) {
        $message->to('test@example.com')
                ->subject('SMTP Test')
                ->from('your-email@gmail.com');
    });
    echo "Email sent successfully!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## 🐛 **Step 6: Enable Detailed Logging**

The code now logs detailed SMTP errors. Check:

1. **Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Look for:**
   - `SMTP Transport Error` - Connection/authentication issues
   - `Error sending test email` - Other email errors
   - SMTP host, port, username in logs

---

## ✅ **Step 7: Common Gmail Error Codes**

### **535-5.7.8: Username and Password not accepted**
- **Solution:** Use App Password, not regular password
- **Check:** 2-Step Verification is enabled

### **Connection timeout**
- **Solution:** Check firewall, try port 465 with SSL
- **Check:** Internet connection

### **530-5.7.0: Authentication required**
- **Solution:** Enable "Less secure app access" (if still available)
- **Better:** Use App Password instead

---

## 🔄 **Step 8: Alternative - Use Port 465 with SSL**

If port 587 doesn't work, try port 465 with SSL:

```json
{
  "smtp_host": "smtp.gmail.com",
  "smtp_port": 465,
  "smtp_username": "your-email@gmail.com",
  "smtp_password": "your-app-password",
  "smtp_encryption": "ssl"  // ← Changed to ssl
}
```

---

## 📝 **Step 9: Check Email Provider Configuration**

1. Go to: `http://localhost:8000/providers`
2. Edit your Gmail provider
3. Verify:
   - ✅ `smtp_host` = `smtp.gmail.com`
   - ✅ `smtp_port` = `587` (or `465`)
   - ✅ `smtp_username` = Full email address
   - ✅ `smtp_password` = App Password (16 chars)
   - ✅ `smtp_encryption` = `tls` (or `ssl` for port 465)

---

## 🎯 **Quick Checklist**

- [ ] Using Gmail App Password (not regular password)
- [ ] 2-Step Verification is enabled on Gmail account
- [ ] SMTP host: `smtp.gmail.com`
- [ ] SMTP port: `587` (TLS) or `465` (SSL)
- [ ] SMTP encryption matches port (`tls` for 587, `ssl` for 465)
- [ ] Username is full email address
- [ ] Firewall allows port 587/465
- [ ] Check Laravel logs for actual errors
- [ ] Test with different email address
- [ ] Check spam/junk folder

---

## 🔗 **Useful Links**

- **Gmail App Passwords:** https://myaccount.google.com/apppasswords
- **Gmail SMTP Settings:** https://support.google.com/mail/answer/7126229
- **Test SMTP Connection:** Use telnet or online SMTP testers

---

## 💡 **Next Steps After Fixing**

1. **Test again** with the test email endpoint
2. **Check logs** for any remaining errors
3. **Verify email** arrives in inbox (check spam too)
4. **Update provider config** if settings changed

---

**Still not working?** Check the Laravel logs - the new error handling will show the exact SMTP error message!

