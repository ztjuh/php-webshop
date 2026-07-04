<?php
/**
 * PayPal Configuration
 * Update these values with your actual PayPal credentials
 */

// PayPal API credentials
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' or 'live'
define('PAYPAL_SANDBOX_API_SIGNATURE', 'YOUR_SANDBOX_API_SIGNATURE');
define('PAYPAL_LIVE_API_SIGNATURE', 'YOUR_LIVE_API_SIGNATURE');
define('PAYPAL_API_USERNAME', 'YOUR_API_USERNAME');
define('PAYPAL_API_PASSWORD', 'YOUR_API_PASSWORD');

// PayPal URLs
define('PAYPAL_SANDBOX_API_URL', 'https://api.sandbox.paypal.com/nvp');
define('PAYPAL_LIVE_API_URL', 'https://api.paypal.com/nvp');
define('PAYPAL_SANDBOX_CHECKOUT_URL', 'https://www.sandbox.paypal.com/checkoutnow?token=');
define('PAYPAL_LIVE_CHECKOUT_URL', 'https://www.paypal.com/checkoutnow?token=');

// Return to your store after PayPal
define('PAYPAL_RETURN_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/paypal-return.php');
define('PAYPAL_CANCEL_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/cart.php');
define('PAYPAL_NOTIFY_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/paypal-notify.php');

// Store currency
define('PAYPAL_CURRENCY', 'EUR');

/**
 * Get PayPal API URL
 */
function getPayPalApiUrl() {
    return PAYPAL_MODE === 'live' ? PAYPAL_LIVE_API_URL : PAYPAL_SANDBOX_API_URL;
}

/**
 * Get PayPal Checkout URL
 */
function getPayPalCheckoutUrl($token) {
    $url = PAYPAL_MODE === 'live' ? PAYPAL_LIVE_CHECKOUT_URL : PAYPAL_SANDBOX_CHECKOUT_URL;
    return $url . $token;
}

/**
 * Make PayPal API request
 */
function makePayPalRequest($method, $params) {
    $api_username = PAYPAL_API_USERNAME;
    $api_password = PAYPAL_API_PASSWORD;
    $api_signature = PAYPAL_MODE === 'live' ? PAYPAL_LIVE_API_SIGNATURE : PAYPAL_SANDBOX_API_SIGNATURE;
    
    $params['METHOD'] = $method;
    $params['VERSION'] = '204.0';
    $params['USER'] = $api_username;
    $params['PWD'] = $api_password;
    $params['SIGNATURE'] = $api_signature;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, getPayPalApiUrl());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        error_log("PayPal CURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    parse_str($response, $response_array);
    return $response_array;
}
?>
