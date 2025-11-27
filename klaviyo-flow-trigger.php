<?php
/**
 * Klaviyo Flow Trigger Without Subscription Email
 * 
 * Add this code to your WordPress theme's functions.php file
 * OR create a custom plugin with this code
 * 
 * Instructions:
 * 1. Replace YOUR_KLAVIYO_PUBLIC_API_KEY with your actual Klaviyo Public API Key
 * 2. Replace YOUR_FLOW_ID with your actual Klaviyo Flow ID (if using direct flow trigger)
 * 3. Adjust the hook name based on your form plugin (see examples below)
 */

// ============================================
// SOLUTION 1: Track Custom Event (Recommended)
// ============================================
// This triggers a flow that uses "Custom Event" as trigger instead of "Added to List"

/**
 * Track Klaviyo Custom Event
 * 
 * @param string $event_name The name of the custom event (must match your flow trigger)
 * @param string $email User email address
 * @param array $properties Additional event properties
 * @return array|WP_Error API response
 */
function klaviyo_track_custom_event($event_name, $email, $properties = array()) {
    $public_api_key = 'YOUR_KLAVIYO_PUBLIC_API_KEY'; // Get from Klaviyo Settings > API Keys
    
    $data = array(
        'token' => $public_api_key,
        'event' => $event_name,
        'customer_properties' => array(
            '$email' => $email
        ),
        'properties' => array_merge(array(
            'source' => 'wordpress',
            'timestamp' => time()
        ), $properties)
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

// ============================================
// SOLUTION 2: Direct Flow Trigger via API
// ============================================
// This directly triggers a specific flow without adding to list

/**
 * Trigger Klaviyo Flow Directly
 * 
 * @param string $email User email address
 * @param string $flow_id Your Klaviyo Flow ID
 * @param array $profile_data Additional profile data
 * @return array|WP_Error API response
 */
function klaviyo_trigger_flow($email, $flow_id, $profile_data = array()) {
    $private_api_key = 'YOUR_KLAVIYO_PRIVATE_API_KEY'; // Get from Klaviyo Settings > API Keys
    
    $data = array(
        'data' => array(
            'type' => 'flow-action',
            'attributes' => array(
                'profile' => array(
                    'data' => array(
                        'type' => 'profile',
                        'attributes' => array_merge(array(
                            'email' => $email
                        ), $profile_data)
                    )
                )
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
            'Authorization' => 'Klaviyo-API-Key ' . $private_api_key,
            'Content-Type' => 'application/json',
            'revision' => '2024-02-15'
        ),
        'timeout' => 10
    ));
    
    return $response;
}

// ============================================
// INTEGRATION EXAMPLES
// ============================================

// Example 1: Hook into Contact Form 7 submission
add_action('wpcf7_mail_sent', 'klaviyo_on_cf7_submit', 10, 1);
function klaviyo_on_cf7_submit($contact_form) {
    $submission = WPCF7_Submission::get_instance();
    if ($submission) {
        $posted_data = $submission->get_posted_data();
        $email = isset($posted_data['your-email']) ? $posted_data['your-email'] : 
                 (isset($posted_data['email']) ? $posted_data['email'] : '');
        
        if (!empty($email) && is_email($email)) {
            // Option A: Track custom event (flow must use "Custom Event" trigger)
            klaviyo_track_custom_event('User Subscribed', $email, array(
                'form_name' => $contact_form->title(),
                'form_id' => $contact_form->id()
            ));
            
            // Option B: Or trigger flow directly (uncomment and set flow_id)
            // klaviyo_trigger_flow($email, 'YOUR_FLOW_ID');
        }
    }
}

// Example 2: Hook into Gravity Forms submission
add_action('gform_after_submission', 'klaviyo_on_gravity_forms_submit', 10, 2);
function klaviyo_on_gravity_forms_submit($entry, $form) {
    // Find email field (adjust field ID as needed)
    $email = rgar($entry, '1'); // Change '1' to your email field ID
    
    if (!empty($email) && is_email($email)) {
        // Option A: Track custom event
        klaviyo_track_custom_event('User Subscribed', $email, array(
            'form_name' => $form['title'],
            'form_id' => $form['id']
        ));
        
        // Option B: Or trigger flow directly
        // klaviyo_trigger_flow($email, 'YOUR_FLOW_ID');
    }
}

// Example 3: Hook into WPForms submission
add_action('wpforms_process_complete', 'klaviyo_on_wpforms_submit', 10, 4);
function klaviyo_on_wpforms_submit($fields, $entry, $form_data, $entry_id) {
    // Find email field
    $email = '';
    foreach ($fields as $field_id => $field) {
        if (isset($field['type']) && $field['type'] === 'email') {
            $email = $field['value'];
            break;
        }
    }
    
    if (!empty($email) && is_email($email)) {
        // Option A: Track custom event
        klaviyo_track_custom_event('User Subscribed', $email, array(
            'form_name' => $form_data['settings']['form_title'],
            'form_id' => $form_data['id']
        ));
        
        // Option B: Or trigger flow directly
        // klaviyo_trigger_flow($email, 'YOUR_FLOW_ID');
    }
}

// Example 4: Hook into Klaviyo form submission (if using Klaviyo embed code)
add_action('wp_footer', 'klaviyo_custom_event_tracker');
function klaviyo_custom_event_tracker() {
    ?>
    <script type="text/javascript">
    // Wait for Klaviyo to load
    if (typeof _learnq !== 'undefined') {
        // Listen for Klaviyo form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Find all Klaviyo forms on the page
            var klaviyoForms = document.querySelectorAll('[data-klaviyo-form]');
            
            klaviyoForms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    // Get email from form
                    var emailInput = form.querySelector('input[type="email"]');
                    if (emailInput && emailInput.value) {
                        // Track custom event
                        _learnq.push(['track', 'User Subscribed', {
                            'email': emailInput.value,
                            'source': 'klaviyo_form'
                        }]);
                    }
                });
            });
        });
    }
    </script>
    <?php
}

// Example 5: Generic form submission handler (for any form)
add_action('wp_ajax_klaviyo_subscribe', 'klaviyo_ajax_subscribe');
add_action('wp_ajax_nopriv_klaviyo_subscribe', 'klaviyo_ajax_subscribe');
function klaviyo_ajax_subscribe() {
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    
    if (empty($email) || !is_email($email)) {
        wp_send_json_error(array('message' => 'Invalid email address'));
        return;
    }
    
    // Option A: Track custom event
    $result = klaviyo_track_custom_event('User Subscribed', $email);
    
    // Option B: Or trigger flow directly
    // $result = klaviyo_trigger_flow($email, 'YOUR_FLOW_ID');
    
    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => 'Failed to trigger flow'));
    } else {
        wp_send_json_success(array('message' => 'Flow triggered successfully'));
    }
}

// ============================================
// HELPER FUNCTION: Get Flow ID from URL
// ============================================
/**
 * Extract Flow ID from Klaviyo flow URL
 * URL format: https://www.klaviyo.com/flow/FLOW_ID_HERE
 */
function get_klaviyo_flow_id_from_url($url) {
    preg_match('/flow\/([a-zA-Z0-9]+)/', $url, $matches);
    return isset($matches[1]) ? $matches[1] : '';
}

// ============================================
// USAGE INSTRUCTIONS
// ============================================
/*
 * STEP 1: Get your Klaviyo API Keys
 * - Go to Klaviyo Dashboard > Account > Settings > API Keys
 * - Copy your Public API Key (for tracking events)
 * - Copy your Private API Key (for direct flow triggers)
 * 
 * STEP 2: Update the code above
 * - Replace 'YOUR_KLAVIYO_PUBLIC_API_KEY' with your actual public key
 * - Replace 'YOUR_KLAVIYO_PRIVATE_API_KEY' with your actual private key
 * - Replace 'YOUR_FLOW_ID' with your actual flow ID (if using direct trigger)
 * 
 * STEP 3: Update your Klaviyo Flow
 * - Go to your flow in Klaviyo
 * - Change trigger from "Added to List" to "Custom Event"
 * - Set event name to "User Subscribed" (or change it in the code above)
 * 
 * STEP 4: Choose the right integration example
 * - Uncomment the example that matches your form plugin
 * - Adjust field IDs/names as needed
 * 
 * STEP 5: Test
 * - Submit a test form
 * - Check Klaviyo > Metrics > Events to see if event is tracked
 * - Check Klaviyo > Flows > Your Flow > Activity to see if flow triggered
 */

