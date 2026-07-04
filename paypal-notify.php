<?php
/**
 * PayPal IPN (Instant Payment Notification) Handler
 * This script handles notifications from PayPal about completed transactions
 */

require_once 'include/config.php';
require_once 'include/database.php';
require_once 'include/paypal-config.php';

// Read the IPN message
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = [];

foreach ($raw_post_array as $keyval) {
    @list($key, $val) = explode('=', $keyval);
    if (isset($val)) {
        $myPost[$key] = urldecode($val);
    } else {
        $myPost[$key] = '';
    }
}

// Log the IPN
file_put_contents('logs/paypal-ipn.log', "\n" . date('Y-m-d H:i:s') . " - " . json_encode($myPost), FILE_APPEND);

// Verify the IPN with PayPal
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $req .= "&$key=" . urlencode($value);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, getPayPalApiUrl());
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$res = curl_exec($ch);
curl_close($ch);

if (strcmp($res, "VERIFIED") == 0) {
    // IPN verified - process the transaction
    
    if ($myPost['txn_type'] == 'web_accept' || $myPost['txn_type'] == 'subscr_payment') {
        // Check payment status
        if ($myPost['payment_status'] == 'Completed') {
            
            // Find order by transaction ID
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE paypal_transaction_id = ?");
            $stmt->execute([$myPost['txn_id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                // Update order status
                updateOrderStatus($order['id'], 'completed');
                file_put_contents('logs/paypal-ipn.log', "\n" . date('Y-m-d H:i:s') . " - Order {$order['id']} marked as completed", FILE_APPEND);
            } else {
                // New transaction - create order if needed
                file_put_contents('logs/paypal-ipn.log', "\n" . date('Y-m-d H:i:s') . " - No order found for transaction {$myPost['txn_id']}", FILE_APPEND);
            }
        }
        elseif ($myPost['payment_status'] == 'Pending') {
            file_put_contents('logs/paypal-ipn.log', "\n" . date('Y-m-d H:i:s') . " - Payment pending: {$myPost['pending_reason']}", FILE_APPEND);
        }
        elseif ($myPost['payment_status'] == 'Failed' || $myPost['payment_status'] == 'Denied' || $myPost['payment_status'] == 'Expired' || $myPost['payment_status'] == 'Voided') {
            // Payment failed - update order
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE paypal_transaction_id = ?");
            $stmt->execute([$myPost['txn_id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                updateOrderStatus($order['id'], 'cancelled');
                file_put_contents('logs/paypal-ipn.log', "\n" . date('Y-m-d H:i:s') . " - Order {$order['id']} marked as cancelled (Status: {$myPost['payment_status']})", FILE_APPEND);
            }
        }
    }
    elseif ($myPost['txn_type'] == 'refund') {
        // Handle refunds
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE paypal_transaction_id = ?");
        $stmt->execute([$myPost['parent_txn_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            file_put_contents('logs/paypal-ipn.log', "\n" . date('Y-m-d H:i:s') . " - Order {$order['id']} refunded", FILE_APPEND);
        }
    }
    
    // Always return 200 OK to PayPal
    http_response_code(200);
} 
else if (strcmp($res, "INVALID") == 0) {
    // IPN not verified
    file_put_contents('logs/paypal-ipn.log', "\n" . date('Y-m-d H:i:s') . " - IPN INVALID", FILE_APPEND);
    http_response_code(200);
}
else {
    // curl error
    file_put_contents('logs/paypal-ipn.log', "\n" . date('Y-m-d H:i:s') . " - CURL Error: " . curl_error($ch), FILE_APPEND);
    http_response_code(200);
}

exit();
?>
