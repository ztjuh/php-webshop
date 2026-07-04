<?php
require_once 'include/config.php';
require_once 'include/session.php';
require_once 'include/database.php';
require_once 'include/header.php';
require_once 'include/navigation.php';

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id) {
    header('Location: index.php');
    exit();
}

$order = getOrder($order_id);

if (!$order) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Order not found.</div></div>";
    require_once 'include/footer.php';
    exit();
}

$order_items = getOrderItems($order_id);

echo "<div class='container mt-5'>";
echo "  <div class='alert alert-success' role='alert'>";
echo "    <h4 class='alert-heading'>Thank You!</h4>";
echo "    <p>Your order has been successfully placed.</p>";
echo "    <hr>";
echo "    <p class='mb-0'><strong>Order ID:</strong> #" . htmlspecialchars($order_id) . "</p>";
echo "  </div>";

echo "  <div class='row'>";
echo "    <div class='col-md-8'>";

echo "      <div class='card mb-3'>";
echo "        <div class='card-header'>";
echo "          <h5>Order Details</h5>";
echo "        </div>";
echo "        <div class='card-body'>";
echo "          <p><strong>Order Date:</strong> " . htmlspecialchars($order['created_at']) . "</p>";
echo "          <p><strong>Order Status:</strong> <span class='badge badge-primary'>" . ucfirst($order['status']) . "</span></p>";
echo "          <p><strong>Payment Method:</strong> " . ucfirst($order['payment_method']) . "</p>";
if ($order['paypal_transaction_id']) {
    echo "          <p><strong>PayPal Transaction ID:</strong> " . htmlspecialchars($order['paypal_transaction_id']) . "</p>";
}
echo "        </div>";
echo "      </div>";

echo "      <div class='card mb-3'>";
echo "        <div class='card-header'>";
echo "          <h5>Items</h5>";
echo "        </div>";
echo "        <div class='card-body'>";
echo "          <table class='table'>";
echo "            <thead>";
echo "              <tr>";
echo "                <th>Product</th>";
echo "                <th>Quantity</th>";
echo "                <th>Price</th>";
echo "                <th>Subtotal</th>";
echo "              </tr>";
echo "            </thead>";
echo "            <tbody>";

$total = 0;
foreach ($order_items as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    echo "              <tr>";
    echo "                <td>" . htmlspecialchars($item['product']) . "</td>";
    echo "                <td>" . $item['quantity'] . "</td>";
    echo "                <td>€ " . number_format($item['price'], 2, ',', '.') . "</td>";
    echo "                <td>€ " . number_format($subtotal, 2, ',', '.') . "</td>";
    echo "              </tr>";
}

echo "            </tbody>";
echo "          </table>";
echo "        </div>";
echo "      </div>";

echo "    </div>";

echo "    <div class='col-md-4'>";

echo "      <div class='card mb-3'>";
echo "        <div class='card-header'>";
echo "          <h5>Customer Information</h5>";
echo "        </div>";
echo "        <div class='card-body'>";
echo "          <p>";
echo "            <strong>" . htmlspecialchars($order['firstname'] . ' ' . $order['lastname']) . "</strong><br>";
echo "            " . htmlspecialchars($order['email']) . "<br>";
echo "          </p>";
echo "        </div>";
echo "      </div>";

if ($order['shipping_address']) {
    echo "      <div class='card mb-3'>";
    echo "        <div class='card-header'>";
    echo "          <h5>Shipping Address</h5>";
    echo "        </div>";
    echo "        <div class='card-body'>";
    echo "          " . htmlspecialchars($order['shipping_address']) . "<br>";
    echo "          " . htmlspecialchars($order['shipping_postal_code']) . " " . htmlspecialchars($order['shipping_city']) . "<br>";
    echo "          " . htmlspecialchars($order['shipping_country']) . "<br>";
    echo "        </div>";
    echo "      </div>";
}

echo "      <div class='card'>";
echo "        <div class='card-header'>";
echo "          <h5>Order Total</h5>";
echo "        </div>";
echo "        <div class='card-body'>";
echo "          <h3 class='text-right'>€ " . number_format($order['total_amount'], 2, ',', '.') . "</h3>";
echo "        </div>";
echo "      </div>";

echo "    </div>";

echo "  </div>";

echo "  <div class='mt-4'>";
echo "    <p>You will receive a confirmation email shortly to " . htmlspecialchars($order['email']) . "</p>";
echo "    <p>Thank you for your purchase!</p>";
echo "    <a href='products.php' class='btn btn-primary'>Continue Shopping</a>";
echo "    <a href='index.php' class='btn btn-outline-primary'>Back to Home</a>";
echo "  </div>";

echo "</div>";

require_once 'include/footer.php';
?>
