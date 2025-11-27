# Klaviyo Flow Without Subscription Confirmation Email

## Problem
You have a Klaviyo flow that triggers when a user is added to a subscriber list. However, when users subscribe, they receive a subscription confirmation email first, and then your flow executes. You want to execute the flow directly without sending the subscription confirmation email.

## Solutions

### Solution 1: Disable Subscription Confirmation Email in Klaviyo Settings ⭐ RECOMMENDED

This is the simplest solution if you want to keep using the "Added to List" trigger.

**Steps:**
1. Log in to your Klaviyo account
2. Go to **Settings** → **Email Settings**
3. Find **"Subscription Confirmation Email"** or **"Double Opt-in"** settings
4. **Disable** the automatic subscription confirmation email
5. Your flow will still trigger when users are added to the list, but they won't receive the confirmation email

**Note:** This affects all lists. If you only want to disable it for specific lists, you'll need to use Solution 2 or 3.

---

### Solution 2: Use a Custom Event Trigger Instead

Instead of triggering the flow when a user is added to a list, trigger it with a custom event.

**Steps in Klaviyo:**
1. Go to **Flows** → Edit your flow
2. Change the trigger from **"Added to List"** to **"Custom Event"**
3. Create a custom event name (e.g., `User Subscribed`, `Newsletter Signup`, `Form Submitted`)
4. Save the flow

**Steps in WordPress:**
When a user submits your Klaviyo signup form, you need to track a custom event instead of (or in addition to) adding them to the list.

**Option A: Using Klaviyo JavaScript SDK (Recommended)**
```javascript
// Add this to your WordPress theme or plugin
<script type="text/javascript">
  var _learnq = _learnq || [];
  
  // When form is submitted
  _learnq.push(['track', 'User Subscribed', {
    'email': 'user@example.com',
    'source': 'wordpress_form'
  }]);
</script>
```

**Option B: Using Klaviyo REST API**
```php
// In your WordPress functions.php or plugin
function trigger_klaviyo_custom_event($email) {
    $api_key = 'YOUR_KLAVIYO_API_KEY';
    $event_name = 'User Subscribed';
    
    $data = array(
        'token' => $api_key,
        'event' => $event_name,
        'customer_properties' => array(
            '$email' => $email
        ),
        'properties' => array(
            'source' => 'wordpress_form'
        )
    );
    
    $response = wp_remote_post('https://a.klaviyo.com/api/track', array(
        'body' => json_encode($data),
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    ));
    
    return $response;
}
```

---

### Solution 3: Use Klaviyo API to Trigger Flow Directly

You can use Klaviyo's Flow API to trigger a flow for a specific person without adding them to a list first.

**Steps:**
1. Get your **Flow ID** from Klaviyo (in the flow URL or settings)
2. Get your **Private API Key** from Klaviyo Settings → API Keys
3. Use the API to trigger the flow

**WordPress Implementation:**
```php
// In your WordPress functions.php or plugin
function trigger_klaviyo_flow($email, $flow_id) {
    $api_key = 'YOUR_KLAVIYO_PRIVATE_API_KEY';
    
    $data = array(
        'data' => array(
            'type' => 'flow-action',
            'attributes' => array(
                'profile' => array(
                    'data' => array(
                        'type' => 'profile',
                        'attributes' => array(
                            'email' => $email
                        )
                    )
                }
            ),
            'relationships' => array(
                'flow' => array(
                    'data' => array(
                        'type' => 'flow',
                        'id' => $flow_id
                    )
                )
            )
        )
    );
    
    $response = wp_remote_post('https://a.klaviyo.com/api/flows/' . $flow_id . '/actions', array(
        'body' => json_encode($data),
        'headers' => array(
            'Authorization' => 'Klaviyo-API-Key ' . $api_key,
            'Content-Type' => 'application/json',
            'revision' => '2024-02-15'
        )
    ));
    
    return $response;
}

// Usage
trigger_klaviyo_flow('user@example.com', 'YOUR_FLOW_ID');
```

**Note:** This requires Klaviyo API v3. Check the latest API documentation for the correct endpoint format.

---

### Solution 4: Modify Flow to Skip Confirmation Email Step

If you want to keep the "Added to List" trigger but remove the confirmation email from the flow itself:

**Steps:**
1. Go to your flow in Klaviyo
2. Find the email step that sends the subscription confirmation
3. Either:
   - **Delete** that email step, OR
   - **Disable** that email step (if there's a disable option)
4. Save the flow

**Note:** This only works if the confirmation email is part of your flow, not if it's a Klaviyo system email.

---

## Recommended Approach

**For most cases, I recommend Solution 1 or Solution 2:**

- **Solution 1** if you want the simplest fix and don't need subscription confirmations
- **Solution 2** if you want more control and can modify your WordPress code to track custom events

## Finding Your Flow ID

To use Solution 3, you need your Flow ID:
1. Go to your flow in Klaviyo
2. Look at the URL: `https://www.klaviyo.com/flow/FLOW_ID_HERE`
3. The Flow ID is in the URL

## Getting Your Klaviyo API Keys

1. Log in to Klaviyo
2. Go to **Account** → **Settings** → **API Keys**
3. Copy your **Private API Key** (for server-side calls)
4. Copy your **Public API Key** (for client-side tracking)

---

## WordPress Integration Example

Here's a complete example for WordPress that uses a custom event:

```php
// Add to functions.php or a custom plugin

// Hook into your form submission (adjust hook name based on your form plugin)
add_action('klaviyo_form_submitted', 'handle_klaviyo_subscription', 10, 1);

function handle_klaviyo_subscription($email) {
    // Option 1: Track custom event (triggers flow with custom event trigger)
    track_klaviyo_event('User Subscribed', $email);
    
    // Option 2: Or trigger flow directly via API
    // trigger_klaviyo_flow($email, 'YOUR_FLOW_ID');
}

function track_klaviyo_event($event_name, $email, $properties = array()) {
    $public_api_key = 'YOUR_KLAVIYO_PUBLIC_API_KEY';
    
    $data = array(
        'token' => $public_api_key,
        'event' => $event_name,
        'customer_properties' => array(
            '$email' => $email
        ),
        'properties' => $properties
    );
    
    $response = wp_remote_post('https://a.klaviyo.com/api/track', array(
        'body' => json_encode($data),
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'timeout' => 10
    ));
    
    return $response;
}
```

---

## Testing

After implementing your chosen solution:

1. **Test the flow:**
   - Submit a test subscription
   - Verify the flow executes
   - Verify NO subscription confirmation email is sent

2. **Check Klaviyo:**
   - Go to **Metrics** → **Events** to see if your custom event is being tracked
   - Go to **Flows** → Your flow → **Activity** to see if it's triggering

---

## Need Help?

If you need help implementing any of these solutions in your WordPress project, let me know which approach you'd like to use, and I can help you write the specific code for your setup.

