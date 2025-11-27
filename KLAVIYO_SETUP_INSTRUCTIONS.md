# Klaviyo Flow Setup Instructions

## Quick Setup Guide

### Step 1: Update Your Klaviyo Flow

1. **Log in to Klaviyo Dashboard**
2. **Go to Flows** → Select your flow
3. **Edit the Trigger:**
   - Click on the trigger step
   - Change from **"Added to List"** to **"Custom Event"**
   - Set the event name to: **"User Subscribed"** (or any name you prefer)
   - Save the flow

### Step 2: Disable Subscription Confirmation Email (Optional)

1. **Go to Settings** → **Email Settings**
2. **Find "Subscription Confirmation Email"** or **"Double Opt-in"**
3. **Disable** it (or set it to Single Opt-in)
4. This prevents the automatic confirmation email from being sent

### Step 3: Get Your API Keys

1. **Go to Account** → **Settings** → **API Keys**
2. **Copy your Public API Key** (starts with `pk_`)
3. **Copy your Private API Key** (starts with `sk_`)

### Step 4: Add Code to WordPress

**Option A: Using PHP (Recommended for server-side)**

1. Open `klaviyo-flow-trigger.php`
2. Replace `YOUR_KLAVIYO_PUBLIC_API_KEY` with your actual public key
3. Replace `YOUR_KLAVIYO_PRIVATE_API_KEY` with your actual private key (if using direct flow trigger)
4. Add the code to your theme's `functions.php` file OR create a custom plugin

**Option B: Using JavaScript**

1. Open `klaviyo-javascript-tracker.js`
2. Add the script to your theme's footer or before `</body>` tag
3. Make sure Klaviyo tracking script is already loaded on your page

### Step 5: Choose Integration Method

Based on your form plugin, uncomment the appropriate example in `klaviyo-flow-trigger.php`:

- **Contact Form 7**: Use `klaviyo_on_cf7_submit`
- **Gravity Forms**: Use `klaviyo_on_gravity_forms_submit`
- **WPForms**: Use `klaviyo_on_wpforms_submit`
- **Klaviyo Embedded Form**: Use `klaviyo_custom_event_tracker`
- **Custom Form**: Use `klaviyo_ajax_subscribe` with AJAX

### Step 6: Test

1. **Submit a test form** with a test email
2. **Check Klaviyo Dashboard:**
   - Go to **Metrics** → **Events** → Look for "User Subscribed" event
   - Go to **Flows** → Your Flow → **Activity** → Check if flow triggered
3. **Verify no subscription confirmation email** was sent

---

## Finding Your Flow ID

If you need to use the direct flow trigger method:

1. Go to your flow in Klaviyo
2. Look at the URL: `https://www.klaviyo.com/flow/FLOW_ID_HERE`
3. Copy the Flow ID from the URL

---

## Troubleshooting

### Flow Not Triggering?

1. **Check Event Name Match:**
   - Event name in code must EXACTLY match the event name in your flow trigger
   - Case-sensitive!

2. **Check API Keys:**
   - Make sure you're using the correct API key type
   - Public key for tracking events
   - Private key for direct flow triggers

3. **Check Klaviyo Script:**
   - Make sure Klaviyo tracking script is loaded on your page
   - Look for `_learnq` in browser console

4. **Check Browser Console:**
   - Open browser developer tools (F12)
   - Look for JavaScript errors
   - Check Network tab for API calls

### Still Getting Confirmation Email?

1. **Disable in Klaviyo Settings:**
   - Settings → Email Settings → Disable subscription confirmation

2. **Check List Settings:**
   - Some lists have their own confirmation settings
   - Check each list's settings individually

3. **Remove from Flow:**
   - If confirmation email is a step in your flow, delete that step

---

## Code Examples

### Simple PHP Example

```php
// Add to functions.php
add_action('wp_footer', function() {
    if (isset($_POST['email'])) {
        $email = sanitize_email($_POST['email']);
        if (is_email($email)) {
            klaviyo_track_custom_event('User Subscribed', $email);
        }
    }
});
```

### Simple JavaScript Example

```javascript
// Add to your form handler
document.getElementById('my-form').addEventListener('submit', function(e) {
    var email = this.querySelector('input[type="email"]').value;
    if (email) {
        _learnq.push(['track', 'User Subscribed', {
            'email': email
        }]);
    }
});
```

---

## Need Help?

If you're still having issues:
1. Check Klaviyo's API documentation
2. Verify your API keys are correct
3. Test with a simple event first
4. Check Klaviyo's event logs in the dashboard

