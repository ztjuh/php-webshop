<?php
require_once 'include/config.php';
require_once 'include/session.php';
require_once 'include/database.php';
require_once 'include/paypal-config.php';

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
$payer_id = filter_input(INPUT_GET, 'PayerID', FILTER_SANITIZE_STRING);
$customer_id = filter_input(INPUT_GET, 'customer_id', FILTER_VALIDATE_INT);

// Check if we have PayPal token and Payer ID
if (!$token || !$payer_id || !$customer_id) {
    error_log("PayPal Return: Missing token ($token), PayerID ($payer_id), or customer_id ($customer_id)");
    header('Location: cart.php');
    exit();
}

// Verify cart still exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$cart_total = getCartTotal();

// Get PayPal transaction details
$paypal_params = [
    'TOKEN' => $token,
    'PAYERID' => $payer_id
];

$response = makePayPalRequest('GetExpressCheckoutDetails', $paypal_params);

if (!isset($response['ACK']) || ($response['ACK'] !== 'Success' && $response['ACK'] !== 'SuccessWithWarning')) {
    error_log("PayPal GetExpressCheckoutDetails Error: " . json_encode($response));
    header('Location: cart.php?error=PayPal error occurred');
    exit();
}

// Complete the PayPal transaction
$do_express_checkout_params = [
    'TOKEN' => $token,
    'PAYERID' => $payer_id,
    'PAYMENTACTION' => 'Sale',
    'AMT' => number_format($cart_total, 2, '.', ''),
    'CURRENCYCODE' => PAYPAL_CURRENCY
];

$do_response = makePayPalRequest('DoExpressCheckoutPayment', $do_express_checkout_params);

if (!isset($do_response['ACK']) || ($do_response['ACK'] !== 'Success' && $do_response['ACK'] !== 'SuccessWithWarning')) {
    error_log("PayPal DoExpressCheckoutPayment Error: " . json_encode($do_response));
    $_SESSION['paypal_error'] = $do_response['L_LONGMESSAGE0'] ?? 'Payment failed. Please try again.';
    header('Location: cart.php');
    exit();
}

// Payment was successful - create order in database
if ($do_response['PAYMENTSTATUS'] === 'Completed' || $do_response['PAYMENTSTATUS'] === 'Processed') {
    
    $customer = getCustomerById($customer_id);
    
    if (!$customer) {
        error_log("Customer not found: $customer_id");
        header('Location: cart.php');
        exit();
    }
    
    // Create order in database
    $order_id = createOrder(
        $customer_id,
        $cart_total,
        'paypal',
        $customer['address'] ?? '',
        $customer['city'] ?? '',
        $customer['postal_code'] ?? '',
        $customer['country'] ?? ''
    );
    
    if ($order_id) {
        // Update with PayPal transaction ID
        updateOrderPayPalTransaction($order_id, $do_response['TRANSACTIONID']);
        
        // Clear the cart
        unset($_SESSION['cart']);
        
        // Redirect to success page
        header('Location: order-success.php?order_id=' . $order_id);
        exit();
    } else {
        error_log("Failed to create order for customer: $customer_id");
        $_SESSION['paypal_error'] = 'Order creation failed. Please contact support.';
        header('Location: cart.php');
        exit();
    }
} else {
    error_log("PayPal Payment Status: " . $do_response['PAYMENTSTATUS']);
    $_SESSION['paypal_error'] = 'Payment was not completed. Status: ' . $do_response['PAYMENTSTATUS'];
    header('Location: cart.php');
    exit();
}
?>
