/**
 * Klaviyo JavaScript Tracker
 * 
 * Add this to your WordPress theme or use it inline
 * This uses Klaviyo's JavaScript SDK to track custom events
 * 
 * Instructions:
 * 1. Make sure Klaviyo tracking script is loaded on your page
 * 2. Replace 'User Subscribed' with your custom event name (must match flow trigger)
 * 3. Add this script to your theme's footer or before closing </body> tag
 */

(function() {
    'use strict';
    
    // Wait for Klaviyo to load
    function initKlaviyoTracker() {
        if (typeof _learnq === 'undefined') {
            console.warn('Klaviyo tracking script not loaded');
            return;
        }
        
        // ============================================
        // OPTION 1: Track on Klaviyo Form Submit
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Method 1: Listen for Klaviyo form submissions
            var klaviyoForms = document.querySelectorAll('[data-klaviyo-form], .klaviyo-form, form[action*="klaviyo"]');
            
            klaviyoForms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    var emailInput = form.querySelector('input[type="email"]');
                    if (emailInput && emailInput.value) {
                        trackKlaviyoEvent('User Subscribed', emailInput.value);
                    }
                }, true); // Use capture phase to catch before Klaviyo processes
            });
            
            // Method 2: Listen for any form with email field
            var allForms = document.querySelectorAll('form');
            allForms.forEach(function(form) {
                // Check if this looks like a newsletter/signup form
                var hasEmailField = form.querySelector('input[type="email"]');
                var hasSubmitButton = form.querySelector('button[type="submit"], input[type="submit"]');
                
                if (hasEmailField && hasSubmitButton) {
                    form.addEventListener('submit', function(e) {
                        var email = hasEmailField.value.trim();
                        if (email && isValidEmail(email)) {
                            // Small delay to ensure Klaviyo processes first
                            setTimeout(function() {
                                trackKlaviyoEvent('User Subscribed', email);
                            }, 100);
                        }
                    });
                }
            });
        });
        
        // ============================================
        // OPTION 2: Manual Trigger Function
        // ============================================
        // Call this function manually from your form handler
        window.triggerKlaviyoFlow = function(email, eventName, properties) {
            eventName = eventName || 'User Subscribed';
            properties = properties || {};
            
            trackKlaviyoEvent(eventName, email, properties);
        };
    }
    
    // ============================================
    // Track Custom Event Function
    // ============================================
    function trackKlaviyoEvent(eventName, email, properties) {
        if (typeof _learnq === 'undefined') {
            console.error('Klaviyo not loaded');
            return false;
        }
        
        if (!email || !isValidEmail(email)) {
            console.error('Invalid email address');
            return false;
        }
        
        var eventData = {
            'email': email,
            'source': 'wordpress',
            'timestamp': new Date().toISOString()
        };
        
        // Merge custom properties
        if (properties && typeof properties === 'object') {
            Object.assign(eventData, properties);
        }
        
        // Track the event
        _learnq.push(['track', eventName, eventData]);
        
        console.log('Klaviyo event tracked:', eventName, eventData);
        return true;
    }
    
    // ============================================
    // Email Validation
    // ============================================
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // ============================================
    // Initialize when Klaviyo is ready
    // ============================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKlaviyoTracker);
    } else {
        initKlaviyoTracker();
    }
    
    // Also try after a delay in case Klaviyo loads late
    setTimeout(initKlaviyoTracker, 1000);
    
})();

/**
 * USAGE EXAMPLES:
 * 
 * 1. Automatic tracking (already set up above):
 *    - Just include this script, it will automatically track when forms are submitted
 * 
 * 2. Manual tracking from your code:
 *    triggerKlaviyoFlow('user@example.com', 'User Subscribed', {
 *        'form_name': 'Newsletter Signup',
 *        'page_url': window.location.href
 *    });
 * 
 * 3. jQuery example (if you use jQuery):
 *    jQuery('#my-form').on('submit', function(e) {
 *        var email = jQuery(this).find('input[type="email"]').val();
 *        triggerKlaviyoFlow(email, 'User Subscribed');
 *    });
 */

