# MySQL Backend & PayPal Integration Guide

This guide will help you set up the MySQL backend for products/customers and integrate PayPal payments into your PHP webshop.

## Overview

The webshop now includes:
- **MySQL Database** - Products, customers, orders, and order items
- **PayPal Integration** - Accept payments via PayPal Express Checkout
- **Session-based Cart** - Shopping cart stored in PHP sessions
- **Order Management** - Track customer orders with PayPal transaction IDs

## Prerequisites

- MySQL Server (already configured in your `include/config.php`)
- PayPal Business Account
- PHP with cURL extension enabled
- SSL certificate (required for PayPal production)

## Step 1: Database Setup

### Create the Database Tables

Run the SQL schema from `database-schema.sql` on your MySQL server:

```bash
mysql -u webshop -p webshop_bootstrap4 < database-schema.sql
```

Or import via phpMyAdmin.

### Verify Tables

You should now have these tables:
- `products` - Product catalog
- `customers` - Customer accounts
- `orders` - Customer orders
- `order_items` - Items in each order
- `menu` - Navigation menu

## Step 2: PayPal Configuration

### Get PayPal API Credentials

1. Log in to your [PayPal Business Account](https://www.paypal.com/signin)
2. Go to **Settings** → **Seller preferences** or **API Access**
3. Generate or copy your API credentials:
   - API Username
   - API Password
   - API Signature

### Configure PayPal in Your Project

Edit `include/paypal-config.php` and update these values:

```php
define('PAYPAL_MODE', 'sandbox'); // Change to 'live' for production
define('PAYPAL_API_USERNAME', 'YOUR_API_USERNAME');
define('PAYPAL_API_PASSWORD', 'YOUR_API_PASSWORD');
define('PAYPAL_SANDBOX_API_SIGNATURE', 'YOUR_SANDBOX_SIGNATURE');
define('PAYPAL_LIVE_API_SIGNATURE', 'YOUR_LIVE_SIGNATURE');
```

### Update Return URLs

The PayPal configuration includes return URLs. Make sure they match your domain:

```php
define('PAYPAL_RETURN_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/paypal-return.php');
define('PAYPAL_CANCEL_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/cart.php');
define('PAYPAL_NOTIFY_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/paypal-notify.php');
```

## Step 3: File Structure

### New Files Added

1. **`include/database.php`** - Database functions for products, customers, and orders
2. **`include/paypal-config.php`** - PayPal API configuration
3. **`products.php`** - Product listing and detail pages
4. **`checkout.php`** - Checkout page with PayPal integration
5. **`paypal-return.php`** - PayPal return handler (payment completion)
6. **`paypal-notify.php`** - PayPal IPN notification handler
7. **`order-success.php`** - Order confirmation page
8. **`database-schema.sql`** - Database schema and sample data

## Step 4: Update Your Navigation

Add links to your `include/navigation.php`:

```php
<li class="nav-item"><a class="nav-link" href="products.php">Shop</a></li>
<li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
```

## Step 5: Create Logs Directory

Create a `logs/` directory in your project root for PayPal IPN logging:

```bash
mkdir logs
chmod 755 logs
```

## Database Functions Reference

### Product Functions

```php
getProducts()              // Get all active products
getProductById($id)        // Get single product by ID
```

### Customer Functions

```php
getCustomerByEmail($email)                    // Get customer by email
getCustomerById($id)                          // Get customer by ID
createCustomer($email, $password, $name)      // Create new customer
verifyCustomerPassword($email, $password)     // Verify login credentials
updateCustomer($id, $data)                    // Update customer info
getCustomerOrders($customer_id)               // Get all customer orders
```

### Order Functions

```php
createOrder($customer_id, $total, $method, ...)   // Create order from cart
getOrder($order_id)                                // Get order details
getOrderItems($order_id)                           // Get items in order
updateOrderStatus($order_id, $status)              // Update order status
updateOrderPayPalTransaction($order_id, $txn_id)  // Update PayPal transaction ID
```

### Cart Functions

```php
getCartTotal()  // Calculate cart total using current product prices
```

## PayPal Flow

### 1. Customer Adds Products to Cart

Products are stored in `$_SESSION['cart']` with format:
```php
$_SESSION['cart'][$product_id] = ['amount' => quantity, 'price' => price];
```

### 2. Customer Proceeds to Checkout

1. Customer clicks "Checkout"
2. Must be logged in (redirects to login if not)
3. Selects shipping address
4. Selects PayPal as payment method

### 3. PayPal Express Checkout

1. `checkout.php` sends cart items to PayPal via SetExpressCheckout
2. Customer is redirected to PayPal to login and authorize
3. Customer returns to `paypal-return.php`

### 4. Payment Completion

1. `paypal-return.php` calls DoExpressCheckoutPayment
2. Order is created in database
3. Cart is cleared
4. Customer sees confirmation on `order-success.php`

### 5. IPN Notification

PayPal sends real-time notifications to `paypal-notify.php` confirming transaction status.

## Testing

### Sandbox Testing

1. Use `PAYPAL_MODE = 'sandbox'` in `paypal-config.php`
2. Create a [PayPal Sandbox Account](https://developer.paypal.com)
3. Use sandbox buyer account for testing payments
4. Check `logs/paypal-ipn.log` for transaction logs

### Test Credentials

PayPal provides test buyer and seller accounts in the Sandbox dashboard.

### Debugging

Check these logs:
- `logs/paypal-ipn.log` - IPN notifications
- PHP error_log (set in `php.ini`)
- Database logs via your MySQL client

## Troubleshooting

### "Missing API credentials"
- Verify `include/paypal-config.php` has your credentials
- Check PAYPAL_MODE matches your credential set

### "Payment failed"
- Check order total matches PayPal amount
- Verify SSL certificate (production)
- Check PayPal account permissions

### "Cart is empty"
- Session may have expired
- Verify `include/session.php` starts session correctly
- Check session timeout settings

### "Order not created after payment"
- Check MySQL connection in `include/config.php`
- Verify `orders` table has INSERT permissions
- Check error_log for SQL errors

## Security Considerations

1. **Always use HTTPS** - Required for PayPal production
2. **Validate Amounts** - Verify cart total matches PayPal confirmation
3. **Verify IPN** - `paypal-notify.php` verifies PayPal signature
4. **Store Transactions** - Save PayPal transaction IDs in database
5. **Protect Credentials** - Never commit API credentials to version control
6. **Sanitize Input** - All user input is validated with `filter_input()`

## Production Deployment

### 1. Switch to Production Mode

Update `include/paypal-config.php`:

```php
define('PAYPAL_MODE', 'live');
```

### 2. Install SSL Certificate

PayPal production requires HTTPS/SSL.

### 3. Update Return URLs

```php
define('PAYPAL_RETURN_URL', 'https://yourdomain.com/paypal-return.php');
define('PAYPAL_NOTIFY_URL', 'https://yourdomain.com/paypal-notify.php');
```

### 4. Test Thoroughly

- Process test transactions
- Check IPN logs
- Verify order creation
- Test order confirmation emails

## Database Schema

### products table

| Field | Type | Description |
|-------|------|-------------|
| id | INT | Product ID |
| product | VARCHAR | Product name |
| description | TEXT | Product details |
| price | DECIMAL(10,2) | Product price |
| stock | INT | Available quantity |
| category | VARCHAR | Product category |
| image_url | VARCHAR | Product image URL |
| active | TINYINT | Is product active |

### customers table

| Field | Type | Description |
|-------|------|-------------|
| id | INT | Customer ID |
| email | VARCHAR | Customer email (unique) |
| password | VARCHAR | Hashed password |
| firstname | VARCHAR | First name |
| lastname | VARCHAR | Last name |
| phone | VARCHAR | Phone number |
| address | VARCHAR | Street address |
| city | VARCHAR | City |
| postal_code | VARCHAR | Postal code |
| country | VARCHAR | Country |

### orders table

| Field | Type | Description |
|-------|------|-------------|
| id | INT | Order ID |
| customer_id | INT | Reference to customer |
| total_amount | DECIMAL(10,2) | Order total |
| status | ENUM | pending/completed/shipped/cancelled |
| payment_method | ENUM | paypal/ideal/other |
| paypal_transaction_id | VARCHAR | PayPal transaction ID |
| shipping_* | VARCHAR | Shipping address fields |
| created_at | TIMESTAMP | Order creation time |

### order_items table

| Field | Type | Description |
|-------|------|-------------|
| id | INT | Item ID |
| order_id | INT | Reference to order |
| product_id | INT | Reference to product |
| quantity | INT | Quantity ordered |
| price | DECIMAL(10,2) | Price at time of order |

## Support

For issues or questions:

1. Check PayPal [Developer Documentation](https://developer.paypal.com/docs/)
2. Review PayPal [API Reference](https://developer.paypal.com/reference/)
3. Check logs for error messages
4. Enable debug mode in `paypal-config.php` if needed

## License

This integration is provided as part of your webshop. Follow PayPal's terms of service for production use.
