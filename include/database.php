<?php
/**
 * Database functions for webshop
 * Handles all product, customer, order, and cart operations
 */

/**
 * Get all active products
 */
function getProducts() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE active = 1 ORDER BY product ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product by ID
 */
function getProductById($product_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching product: " . $e->getMessage());
        return null;
    }
}

/**
 * Get customer by email
 */
function getCustomerByEmail($email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching customer: " . $e->getMessage());
        return null;
    }
}

/**
 * Get customer by ID
 */
function getCustomerById($customer_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching customer: " . $e->getMessage());
        return null;
    }
}

/**
 * Create new customer
 */
function createCustomer($email, $password, $firstname = '', $lastname = '') {
    global $pdo;
    try {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO customers (email, password, firstname, lastname) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $hashed_password, $firstname, $lastname]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating customer: " . $e->getMessage());
        return false;
    }
}

/**
 * Verify customer password
 */
function verifyCustomerPassword($email, $password) {
    $customer = getCustomerByEmail($email);
    if ($customer && password_verify($password, $customer['password'])) {
        return $customer;
    }
    return false;
}

/**
 * Update customer info
 */
function updateCustomer($customer_id, $data) {
    global $pdo;
    try {
        $allowed_fields = ['firstname', 'lastname', 'phone', 'address', 'city', 'postal_code', 'country'];
        $update_fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $update_fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        if (empty($update_fields)) {
            return false;
        }
        
        $values[] = $customer_id;
        $stmt = $pdo->prepare("UPDATE customers SET " . implode(', ', $update_fields) . " WHERE id = ?");
        $stmt->execute($values);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating customer: " . $e->getMessage());
        return false;
    }
}

/**
 * Create order from cart
 */
function createOrder($customer_id, $total_amount, $payment_method = 'paypal', $shipping_address = '', $shipping_city = '', $shipping_postal_code = '', $shipping_country = '') {
    global $pdo;
    
    if (empty($_SESSION['cart'])) {
        return false;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (customer_id, total_amount, payment_method, status, shipping_address, shipping_city, shipping_postal_code, shipping_country)
            VALUES (?, ?, ?, 'pending', ?, ?, ?, ?)
        ");
        $stmt->execute([$customer_id, $total_amount, $payment_method, $shipping_address, $shipping_city, $shipping_postal_code, $shipping_country]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert order items from cart
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt->execute([$order_id, $product_id, $item['amount'], $item['price'] ?? 0]);
            
            // Update product stock
            $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $update_stmt->execute([$item['amount'], $product_id]);
        }
        
        $pdo->commit();
        return $order_id;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error creating order: " . $e->getMessage());
        return false;
    }
}

/**
 * Get order by ID with customer details
 */
function getOrder($order_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, c.email, c.firstname, c.lastname 
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching order: " . $e->getMessage());
        return null;
    }
}

/**
 * Get order items with product details
 */
function getOrderItems($order_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT oi.*, p.product, p.description
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching order items: " . $e->getMessage());
        return [];
    }
}

/**
 * Update order status
 */
function updateOrderStatus($order_id, $status) {
    global $pdo;
    try {
        $allowed_statuses = ['pending', 'completed', 'shipped', 'cancelled'];
        if (!in_array($status, $allowed_statuses)) {
            return false;
        }
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

/**
 * Update PayPal transaction ID
 */
function updateOrderPayPalTransaction($order_id, $transaction_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE orders SET paypal_transaction_id = ?, status = 'completed' WHERE id = ?");
        $stmt->execute([$transaction_id, $order_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error updating PayPal transaction: " . $e->getMessage());
        return false;
    }
}

/**
 * Get customer orders
 */
function getCustomerOrders($customer_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching customer orders: " . $e->getMessage());
        return [];
    }
}

/**
 * Calculate cart total with current product prices
 */
function getCartTotal() {
    global $pdo;
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $product = getProductById($product_id);
        if ($product) {
            $total += $product['price'] * $item['amount'];
            // Store current price in session for order creation
            $_SESSION['cart'][$product_id]['price'] = $product['price'];
        }
    }
    return $total;
}
?>
