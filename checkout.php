<?php
require_once 'include/config.php';
require_once 'include/session.php';
require_once 'include/database.php';
require_once 'include/paypal-config.php';
require_once 'include/header.php';
require_once 'include/navigation.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>Please <a href='#' data-toggle='modal' data-target='#popUpLogin'>login</a> to checkout.</div></div>";
    require_once 'include/footer.php';
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<div class='container mt-5'><div class='alert alert-info'>Your cart is empty. <a href='products.php'>Continue shopping</a></div></div>";
    require_once 'include/footer.php';
    exit();
}

$customer = getCustomerById($_SESSION['customer_id']);
$cart_total = getCartTotal();
$method = filter_input(INPUT_GET, 'method', FILTER_SANITIZE_STRING) ?? 'paypal';

// Handle PayPal SetExpressCheckout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $method === 'paypal') {
    
    // Prepare cart items for PayPal
    $item_count = 0;
    $paypal_items = [];
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $product = getProductById($product_id);
        if ($product) {
            $paypal_items[] = [
                'NAME' => $product['product'],
                'QTY' => $item['amount'],
                'AMT' => number_format($product['price'], 2, '.', ''),
                'NUMBER' => $product_id
            ];
            $item_count++;
        }
    }
    
    // Build PayPal request
    $paypal_params = [
        'RETURNURL' => PAYPAL_RETURN_URL . '?customer_id=' . $_SESSION['customer_id'],
        'CANCELURL' => PAYPAL_CANCEL_URL,
        'PAYMENTACTION' => 'Sale',
        'CURRENCYCODE' => PAYPAL_CURRENCY,
        'AMT' => number_format($cart_total, 2, '.', ''),
        'ITEMAMT' => number_format($cart_total, 2, '.', ''),
        'SHIPPINGAMT' => '0.00',
        'TAXAMT' => '0.00',
        'DESC' => 'Webshop Order - ' . date('Y-m-d H:i:s'),
        'INVNUM' => 'ORDER-' . $_SESSION['customer_id'] . '-' . time(),
        'NOTIFYURL' => PAYPAL_NOTIFY_URL,
        'NOSHIPPING' => '2'
    ];
    
    // Add items to request
    $i = 0;
    foreach ($paypal_items as $item) {
        $paypal_params['L_NAME' . $i] = $item['NAME'];
        $paypal_params['L_QTY' . $i] = $item['QTY'];
        $paypal_params['L_AMT' . $i] = $item['AMT'];
        $i++;
    }
    
    // Add customer info
    if ($customer) {
        $paypal_params['EMAIL'] = $customer['email'];
        $paypal_params['FIRSTNAME'] = $customer['firstname'] ?? '';
        $paypal_params['LASTNAME'] = $customer['lastname'] ?? '';
        $paypal_params['STREET'] = $customer['address'] ?? '';
        $paypal_params['CITY'] = $customer['city'] ?? '';
        $paypal_params['STATE'] = '';
        $paypal_params['ZIP'] = $customer['postal_code'] ?? '';
        $paypal_params['COUNTRYCODE'] = $customer['country'] ?? '';
    }
    
    // Make PayPal API call
    $response = makePayPalRequest('SetExpressCheckout', $paypal_params);
    
    if (isset($response['ACK']) && ($response['ACK'] === 'Success' || $response['ACK'] === 'SuccessWithWarning')) {
        $_SESSION['paypal_token'] = $response['TOKEN'];
        $_SESSION['pending_order_customer_id'] = $_SESSION['customer_id'];
        $_SESSION['pending_order_method'] = 'paypal';
        
        header('Location: ' . getPayPalCheckoutUrl($response['TOKEN']));
        exit();
    } else {
        $error = $response['L_LONGMESSAGE0'] ?? 'PayPal error occurred. Please try again.';
        error_log("PayPal SetExpressCheckout Error: " . json_encode($response));
    }
}

echo "<div class='container mt-5'>";
echo "  <h3>Checkout</h3>";
echo "  <div class='row'>";

// Order Summary
echo "    <div class='col-md-8'>";

if (isset($error)) {
    echo "      <div class='alert alert-danger'>$error</div>";
}

echo "      <h5>Shipping Address</h5>";
echo "      <form method='POST'>";
echo "        <div class='form-group'>";
echo "          <label>Address</label>";
echo "          <input type='text' name='shipping_address' class='form-control' value='" . htmlspecialchars($customer['address'] ?? '') . "'>";
echo "        </div>";
echo "        <div class='form-group'>";
echo "          <label>City</label>";
echo "          <input type='text' name='shipping_city' class='form-control' value='" . htmlspecialchars($customer['city'] ?? '') . "'>";
echo "        </div>";
echo "        <div class='form-group'>";
echo "          <label>Postal Code</label>";
echo "          <input type='text' name='shipping_postal_code' class='form-control' value='" . htmlspecialchars($customer['postal_code'] ?? '') . "'>";
echo "        </div>";
echo "        <div class='form-group'>";
echo "          <label>Country</label>";
echo "          <input type='text' name='shipping_country' class='form-control' value='" . htmlspecialchars($customer['country'] ?? '') . "'>";
echo "        </div>";

echo "        <h5 class='mt-4'>Payment Method</h5>";
echo "        <div class='form-check'>";
echo "          <input class='form-check-input' type='radio' name='method' value='paypal' id='paypal' checked>";
echo "          <label class='form-check-label' for='paypal'>";
echo "            <img src='https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg' alt='PayPal' style='height:30px;'>";
echo "          </label>";
echo "        </div>";

echo "        <button type='submit' class='btn btn-success btn-lg mt-4' name='paypal_submit'>Proceed to PayPal</button>";
echo "        <a href='cart.php' class='btn btn-outline-secondary btn-lg mt-4'>Back to Cart</a>";
echo "      </form>";

echo "    </div>";

// Cart Summary
echo "    <div class='col-md-4'>";
echo "      <div class='card'>";
echo "        <div class='card-header'>";
echo "          <h5>Order Summary</h5>";
echo "        </div>";
echo "        <div class='card-body'>";
echo "          <table class='table table-sm'>";

foreach ($_SESSION['cart'] as $product_id => $item) {
    $product = getProductById($product_id);
    if ($product) {
        $subtotal = $product['price'] * $item['amount'];
        echo "            <tr>";
        echo "              <td>" . htmlspecialchars($product['product']) . "</td>";
        echo "              <td class='text-right'>" . $item['amount'] . "x</td>";
        echo "              <td class='text-right'>€ " . number_format($subtotal, 2, ',', '.') . "</td>";
        echo "            </tr>";
    }
}

echo "            <tr class='border-top'>";
echo "              <td colspan='3' class='text-right'>";
echo "                <strong>Total: € " . number_format($cart_total, 2, ',', '.') . "</strong>";
echo "              </td>";
echo "            </tr>";
echo "          </table>";
echo "        </div>";
echo "      </div>";
echo "    </div>";

echo "  </div>";
echo "</div>";

require_once 'include/footer.php';
?>
