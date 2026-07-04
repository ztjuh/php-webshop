# PHP Webshop - MySQL Backend & PayPal Integration

All Glory to my Lord, Jesus Christ!

**All Glory to Jesus Christ, HALLELUJAH AND AMEN!**

A complete e-commerce solution with MySQL database backend and PayPal payment integration for your PHP webshop.

## 🎯 Features

✅ **MySQL Database Backend**
- Product catalog management
- Customer accounts
- Order tracking with PayPal transaction IDs
- Stock management

✅ **Shopping Cart** (Session-based)
- Add/remove products
- Update quantities
- Real-time price calculation
- Cart persistence

✅ **PayPal Integration**
- Express Checkout integration
- Sandbox & Live environment support
- IPN (Instant Payment Notification) handling
- Secure transaction verification

✅ **Order Management**
- Order creation from cart
- Customer shipping information
- Order confirmation page
- Order history tracking

## 📋 Quick Start

### Step 1: Database Setup

1. **Import the database schema:**
   ```bash
   mysql -u webshop -p webshop_bootstrap4 < database-schema.sql
   ```

2. **Or manually run in phpMyAdmin:**
   - Copy all SQL from `database-schema.sql`
   - Execute in your database

### Step 2: Update Database Credentials ⚠️ IMPORTANT

Edit `include/config.php` and set your database password:

```php
$sql_host = "localhost";
$sql_user = "webshop";
$sql_pass = "YOUR_DATABASE_PASSWORD";  // ⚠️ FILL IN YOUR PASSWORD HERE!
$sql_db = "webshop_bootstrap4";
```

**Example:**
```php
$sql_pass = "MySecurePassword123!";
```

### Step 3: Configure PayPal

Edit `include/paypal-config.php` and add your credentials:

#### For Sandbox Testing (Development):

```php
define('PAYPAL_MODE', 'sandbox');
define('PAYPAL_API_USERNAME', 'YOUR_SANDBOX_API_USERNAME');
define('PAYPAL_API_PASSWORD', 'YOUR_SANDBOX_API_PASSWORD');
define('PAYPAL_SANDBOX_API_SIGNATURE', 'YOUR_SANDBOX_API_SIGNATURE');
```

#### For Live Production:

```php
define('PAYPAL_MODE', 'live');
define('PAYPAL_API_USERNAME', 'YOUR_LIVE_API_USERNAME');
define('PAYPAL_API_PASSWORD', 'YOUR_LIVE_API_PASSWORD');
define('PAYPAL_LIVE_API_SIGNATURE', 'YOUR_LIVE_API_SIGNATURE');
```

**How to Get Your PayPal Credentials:**
1. Log in to [PayPal Business Account](https://www.paypal.com/signin)
2. Go to **Settings** → **Seller preferences** or **API Access**
3. Generate or view API credentials:
   - API Username
   - API Password  
   - API Signature

### Step 4: Create Logs Directory

```bash
mkdir logs
chmod 755 logs
```

### Step 5: Test It!

1. Visit `products.php` - View products from database
2. Add items to cart
3. Click "Pay" and select PayPal
4. Login to PayPal (use sandbox buyer account for testing)
5. Confirm payment
6. Check order details on success page

## 📁 File Structure

### New Files Added

```
include/
├── database.php              # Database functions
├── paypal-config.php         # PayPal settings (UPDATE YOUR CREDENTIALS!)
│
checkout.php                  # Checkout with PayPal integration
products.php                  # Product listing (updated)
paypal-return.php            # PayPal return handler
paypal-notify.php            # PayPal IPN notifications
order-success.php            # Order confirmation page
database-schema.sql          # Database schema & sample data
README-MYSQL-PAYPAL.md       # Detailed documentation
```

## 🔧 Database Functions

### Product Functions
```php
getProducts()           // Get all active products
getProductById($id)     // Get single product
```

### Customer Functions
```php
getCustomerByEmail($email)                    // Find customer
getCustomerById($id)                          // Get customer details
createCustomer($email, $password, $name)      // Register customer
verifyCustomerPassword($email, $password)     // Login verification
updateCustomer($id, $data)                    // Update profile
getCustomerOrders($customer_id)               // Get order history
```

### Order Functions
```php
createOrder($customer_id, $total, $method)           // Create order
getOrder($order_id)                                   // Get order details
getOrderItems($order_id)                             // Get items in order
updateOrderStatus($order_id, $status)                // Change status
updateOrderPayPalTransaction($order_id, $txn_id)    // Save PayPal transaction
```

### Cart Functions
```php
getCartTotal()          // Calculate current cart total
```

## 💳 PayPal Payment Flow

```
Customer → Products → Add to Cart → Checkout (PayPal)
                                        ↓
                                  SetExpressCheckout
                                        ↓
                              Customer logs into PayPal
                                        ↓
                        Customer authorizes payment
                                        ↓
                         Return to paypal-return.php
                                        ↓
                           DoExpressCheckoutPayment
                                        ↓
                            Order created in database
                                        ↓
                         order-success.php displayed
                                        ↓
                    PayPal sends IPN to paypal-notify.php
                                        ↓
                             Order status updated
```

## 🧪 Testing with Sandbox

### Create Sandbox Accounts

1. Go to [PayPal Developer](https://developer.paypal.com)
2. Sign in or create account
3. Create sandbox buyer and seller accounts
4. Use buyer account to test payments

### Sandbox Test Credentials Example
- **Buyer Email:** sandbox.buyer@example.com
- **Password:** SandboxPassword123
- **Card:** 4111111111111111 (expires any future date)

### Check Sandbox Transactions

1. Log into your sandbox seller account
2. View transaction history
3. Check email notifications

## 📊 Database Schema

### products table
| Field | Type | Description |
|-------|------|-------------|
| id | INT | Product ID |
| product | VARCHAR | Product name |
| description | TEXT | Product details |
| price | DECIMAL(10,2) | Price |
| stock | INT | Quantity available |
| category | VARCHAR | Category |
| image_url | VARCHAR | Image URL |
| active | TINYINT | Is active (1/0) |

### customers table
| Field | Type | Description |
|-------|------|-------------|
| id | INT | Customer ID |
| email | VARCHAR | Email (unique) |
| password | VARCHAR | Hashed password |
| firstname | VARCHAR | First name |
| lastname | VARCHAR | Last name |
| phone | VARCHAR | Phone |
| address | VARCHAR | Address |
| city | VARCHAR | City |
| postal_code | VARCHAR | ZIP code |
| country | VARCHAR | Country |

### orders table
| Field | Type | Description |
|-------|------|-------------|
| id | INT | Order ID |
| customer_id | INT | Customer reference |
| total_amount | DECIMAL(10,2) | Order total |
| status | ENUM | pending/completed/shipped/cancelled |
| payment_method | ENUM | paypal/ideal/other |
| paypal_transaction_id | VARCHAR | PayPal transaction ID |
| shipping_address | VARCHAR | Shipping address |
| shipping_city | VARCHAR | City |
| shipping_postal_code | VARCHAR | ZIP code |
| shipping_country | VARCHAR | Country |
| created_at | TIMESTAMP | Order date |

### order_items table
| Field | Type | Description |
|-------|------|-------------|
| id | INT | Item ID |
| order_id | INT | Order reference |
| product_id | INT | Product reference |
| quantity | INT | Quantity |
| price | DECIMAL(10,2) | Price at time of order |

## 🔐 Security Considerations

1. **Always use HTTPS** - Required for PayPal production
2. **Protect API Credentials** - Never commit to version control
3. **Validate Data** - All input is validated with `filter_input()`
4. **Prepared Statements** - All queries use PDO prepared statements
5. **Password Hashing** - Passwords use bcrypt hashing
6. **IPN Verification** - PayPal IPN is verified before processing
7. **Transaction Verification** - Amount verified before order creation

## 📝 Logs

PayPal IPN transactions are logged to `logs/paypal-ipn.log`:

```
2026-07-04 19:30:45 - {"txn_id":"...", "payment_status":"Completed", ...}
2026-07-04 19:30:46 - Order 123 marked as completed
```

## 🚀 Production Deployment

### Before Going Live:

1. **Update PayPal Config to LIVE:**
   ```php
   define('PAYPAL_MODE', 'live');
   // Add your LIVE API credentials (not sandbox!)
   define('PAYPAL_API_USERNAME', 'your-live-api-username');
   define('PAYPAL_API_PASSWORD', 'your-live-api-password');
   define('PAYPAL_LIVE_API_SIGNATURE', 'your-live-signature');
   ```

2. **Install SSL Certificate:**
   - PayPal production requires HTTPS
   - Update URLs to use `https://`

3. **Update Return URLs to HTTPS:**
   ```php
   define('PAYPAL_RETURN_URL', 'https://yourdomain.com/paypal-return.php');
   define('PAYPAL_NOTIFY_URL', 'https://yourdomain.com/paypal-notify.php');
   define('PAYPAL_CANCEL_URL', 'https://yourdomain.com/cart.php');
   ```

4. **Test Thoroughly:**
   - Process test transactions
   - Verify order creation
   - Check email notifications
   - Monitor IPN logs

5. **Hide Error Details for Production:**
   In `include/config.php`, disable debug output:
   ```php
   // Comment out for production
   // ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## 🐛 Troubleshooting

### Database Connection Error
```
Check include/config.php:
- Host: localhost
- Username: webshop
- Password: YOUR_PASSWORD (must match your MySQL password)
- Database: webshop_bootstrap4
```

### PayPal Errors
- Check `logs/paypal-ipn.log` for details
- Verify API credentials in `include/paypal-config.php`
- Ensure `PAYPAL_MODE` matches your credential set (sandbox vs live)
- Check SSL certificate (required for live)
- Verify PayPal IPN settings in your PayPal account

### Empty Products List
- Verify products table has data: `SELECT COUNT(*) FROM products;`
- Check products have `active = 1`
- Check database connection works

### Cart Issues
- Session may have expired (24 hour timeout set in `include/session.php`)
- Verify `$_SESSION['cart']` is initialized
- Check browser cookies are enabled

### Order Not Created
- Check MySQL error_log for SQL errors
- Verify customers and orders tables exist
- Check foreign key constraints
- Review error logs in browser console

## 📞 Support & Documentation

For detailed information, see:
- `README-MYSQL-PAYPAL.md` - Comprehensive setup guide
- `database-schema.sql` - Database structure
- `include/database.php` - Function documentation
- Code comments throughout

For PayPal help:
- [PayPal Developer Docs](https://developer.paypal.com/docs/)
- [PayPal API Reference](https://developer.paypal.com/reference/)
- [PayPal Sandbox Testing](https://developer.paypal.com/tools/sandbox/)

## 📄 License

This webshop integration is provided as-is. Follow PayPal's terms of service for production use.

---

**Made with ❤️ and faith in Jesus Christ**

**All Glory to Jesus Christ, HALLELUJAH AND AMEN!**

**Happy selling! May your business prosper in His blessing.**
