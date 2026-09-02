# 💳 PayMongo GCash Integration Guide

## Overview
PayMongo is a payment gateway that supports GCash, cards, and other Philippine payment methods. This guide covers the complete implementation for PeakPH Commerce.

## 1. PayMongo Account Setup

### 1.1 Registration
1. Visit [PayMongo Dashboard](https://dashboard.paymongo.com)
2. Create account with business details
3. Complete verification process
4. Get API credentials from Settings > Developers

### 1.2 API Credentials
```env
# Test Environment
PAYMONGO_SECRET_KEY=sk_test_xxxxx
PAYMONGO_PUBLIC_KEY=pk_test_xxxxx

# Live Environment (after approval)
PAYMONGO_SECRET_KEY=sk_live_xxxxx
PAYMONGO_PUBLIC_KEY=pk_live_xxxxx
```

## 2. Database Schema Updates

### 2.1 Update `payments` Table
```sql
-- Add PayMongo specific fields
ALTER TABLE payments ADD COLUMN paymongo_payment_intent_id VARCHAR(100) DEFAULT NULL;
ALTER TABLE payments ADD COLUMN paymongo_source_id VARCHAR(100) DEFAULT NULL;
ALTER TABLE payments ADD COLUMN payment_gateway ENUM('cod','paymongo_gcash','paymongo_card','bank_transfer') DEFAULT 'cod';
ALTER TABLE payments ADD COLUMN gateway_fee DECIMAL(10,2) DEFAULT 0.00;

-- Add indexes for PayMongo fields
CREATE INDEX idx_payments_intent ON payments(paymongo_payment_intent_id);
CREATE INDEX idx_payments_gateway ON payments(payment_gateway);
```

### 2.2 Create PayMongo Webhooks Table
```sql
CREATE TABLE paymongo_webhooks (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    webhook_id VARCHAR(100) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    payment_intent_id VARCHAR(100) DEFAULT NULL,
    source_id VARCHAR(100) DEFAULT NULL,
    status VARCHAR(30) NOT NULL,
    payload TEXT NOT NULL,
    processed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL DEFAULT NULL
);

CREATE INDEX idx_webhook_intent ON paymongo_webhooks(payment_intent_id);
CREATE INDEX idx_webhook_status ON paymongo_webhooks(status);
```

## 3. PayMongo PHP Library

### 3.1 Installation via Composer
```bash
# Navigate to project root
cd c:\xampp\htdocs\PeakPH_Commerce

# Install PayMongo PHP library
composer require paymongo/paymongo-php
```

### 3.2 Manual Installation (Alternative)
Download PayMongo PHP SDK and place in `includes/paymongo/`

## 4. Configuration Files

### 4.1 Environment Configuration
Create `config/paymongo.php`:
```php
<?php
// PayMongo Configuration
return [
    'secret_key' => 'sk_test_your_secret_key',
    'public_key' => 'pk_test_your_public_key',
    'base_url' => 'https://api.paymongo.com/v1',
    'webhook_signature_key' => 'whsec_your_webhook_secret',
    'gcash_fee_rate' => 0.035, // 3.5% for GCash
    'card_fee_rate' => 0.035, // 3.5% for Cards
    'test_mode' => true, // Set false for production
];
```

### 4.2 PayMongo Helper Class
Create `includes/PayMongoHelper.php`:
```php
<?php
class PayMongoHelper {
    private $secret_key;
    private $public_key;
    private $base_url;
    
    public function __construct() {
        $config = include('../config/paymongo.php');
        $this->secret_key = $config['secret_key'];
        $this->public_key = $config['public_key'];
        $this->base_url = $config['base_url'];
    }
    
    // Create Payment Intent for GCash
    public function createPaymentIntent($amount, $currency = 'PHP', $description = '') {
        $url = $this->base_url . '/payment_intents';
        
        $data = [
            'data' => [
                'attributes' => [
                    'amount' => $amount * 100, // Convert to centavos
                    'payment_method_allowed' => ['gcash'],
                    'payment_method_options' => [
                        'gcash' => [
                            'redirect' => [
                                'success' => 'https://yoursite.com/payment/success',
                                'failed' => 'https://yoursite.com/payment/failed'
                            ]
                        ]
                    ],
                    'currency' => $currency,
                    'description' => $description,
                    'capture_type' => 'automatic'
                ]
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    // Create GCash Source
    public function createGCashSource($amount, $payment_intent_id, $redirect_urls) {
        $url = $this->base_url . '/sources';
        
        $data = [
            'data' => [
                'attributes' => [
                    'amount' => $amount * 100,
                    'redirect' => $redirect_urls,
                    'type' => 'gcash',
                    'currency' => 'PHP'
                ]
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    // Attach Source to Payment Intent
    public function attachSource($payment_intent_id, $source_id) {
        $url = $this->base_url . "/payment_intents/{$payment_intent_id}/attach";
        
        $data = [
            'data' => [
                'attributes' => [
                    'payment_method' => $source_id
                ]
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    // Retrieve Payment Intent
    public function getPaymentIntent($payment_intent_id) {
        $url = $this->base_url . "/payment_intents/{$payment_intent_id}";
        return $this->makeRequest('GET', $url);
    }
    
    // Make HTTP Request
    private function makeRequest($method, $url, $data = null) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'authorization: Basic ' . base64_encode($this->secret_key . ':'),
                'content-type: application/json'
            ]
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new Exception('PayMongo API Error: ' . ($decoded['errors'][0]['detail'] ?? 'Unknown error'));
        }
        
        return $decoded;
    }
}
?>
```

## 5. Checkout Integration

### 5.1 Update checkout.php
Add GCash payment option:
```html
<!-- Payment Method Selection -->
<div class="payment-methods">
    <h5>Payment Method</h5>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
        <label class="form-check-label" for="cod">
            💵 Cash on Delivery (COD)
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="paymongo_gcash">
        <label class="form-check-label" for="gcash">
            📱 GCash (via PayMongo)
            <small class="text-muted d-block">Instant payment with GCash</small>
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_method" id="card" value="paymongo_card">
        <label class="form-check-label" for="card">
            💳 Credit/Debit Card (via PayMongo)
        </label>
    </div>
</div>

<!-- Payment Summary -->
<div id="payment-summary" class="mt-3">
    <div class="d-flex justify-content-between">
        <span>Subtotal:</span>
        <span>₱<span id="subtotal-amount">0.00</span></span>
    </div>
    <div class="d-flex justify-content-between" id="gateway-fee-row" style="display: none;">
        <span>Gateway Fee:</span>
        <span>₱<span id="gateway-fee">0.00</span></span>
    </div>
    <hr>
    <div class="d-flex justify-content-between fw-bold">
        <span>Total:</span>
        <span>₱<span id="total-amount">0.00</span></span>
    </div>
</div>
```

### 5.2 JavaScript for Payment Calculation
```javascript
// Calculate gateway fees
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const subtotal = parseFloat(document.getElementById('subtotal-amount').textContent);
        const feeRow = document.getElementById('gateway-fee-row');
        const feeAmount = document.getElementById('gateway-fee');
        const totalAmount = document.getElementById('total-amount');
        
        if (this.value === 'paymongo_gcash' || this.value === 'paymongo_card') {
            const fee = subtotal * 0.035; // 3.5% gateway fee
            feeAmount.textContent = fee.toFixed(2);
            feeRow.style.display = 'flex';
            totalAmount.textContent = (subtotal + fee).toFixed(2);
        } else {
            feeRow.style.display = 'none';
            totalAmount.textContent = subtotal.toFixed(2);
        }
    });
});
```

## 6. Payment Processing

### 6.1 Create `payment/process_payment.php`
```php
<?php
require_once('../includes/db.php');
require_once('../includes/PayMongoHelper.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../checkout.php');
    exit;
}

$order_id = $_POST['order_id'] ?? null;
$payment_method = $_POST['payment_method'] ?? 'cod';
$total_amount = floatval($_POST['total_amount'] ?? 0);

if (!$order_id || $total_amount <= 0) {
    header('Location: ../checkout.php?error=invalid_data');
    exit;
}

try {
    if (in_array($payment_method, ['paymongo_gcash', 'paymongo_card'])) {
        $paymongo = new PayMongoHelper();
        
        // Create payment record
        $gateway_fee = $total_amount * 0.035;
        $final_amount = $total_amount + $gateway_fee;
        
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, gateway_fee, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->bind_param("iisdd", $order_id, $_SESSION['user_id'], $payment_method, $final_amount, $gateway_fee);
        $stmt->execute();
        $payment_id = $conn->insert_id;
        
        // Create PayMongo Payment Intent
        $description = "Order #$order_id - PeakPH Commerce";
        $payment_intent = $paymongo->createPaymentIntent($final_amount, 'PHP', $description);
        
        if ($payment_intent && isset($payment_intent['data']['id'])) {
            $intent_id = $payment_intent['data']['id'];
            
            // Update payment with PayMongo intent ID
            $stmt = $conn->prepare("UPDATE payments SET paymongo_payment_intent_id = ? WHERE id = ?");
            $stmt->bind_param("si", $intent_id, $payment_id);
            $stmt->execute();
            
            if ($payment_method === 'paymongo_gcash') {
                // Create GCash source
                $redirect_urls = [
                    'success' => 'https://yoursite.com/payment/success.php?payment_id=' . $payment_id,
                    'failed' => 'https://yoursite.com/payment/failed.php?payment_id=' . $payment_id
                ];
                
                $source = $paymongo->createGCashSource($final_amount, $intent_id, $redirect_urls);
                
                if ($source && isset($source['data']['id'])) {
                    $source_id = $source['data']['id'];
                    
                    // Update payment with source ID
                    $stmt = $conn->prepare("UPDATE payments SET paymongo_source_id = ? WHERE id = ?");
                    $stmt->bind_param("si", $source_id, $payment_id);
                    $stmt->execute();
                    
                    // Attach source to payment intent
                    $attached = $paymongo->attachSource($intent_id, $source_id);
                    
                    // Redirect to GCash
                    if (isset($source['data']['attributes']['redirect']['checkout_url'])) {
                        header('Location: ' . $source['data']['attributes']['redirect']['checkout_url']);
                        exit;
                    }
                }
            }
        }
        
        throw new Exception('Failed to create PayMongo payment');
        
    } else {
        // COD Payment
        $stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, payment_method, amount, status, created_at) VALUES (?, ?, 'cod', ?, 'Pending', NOW())");
        $stmt->bind_param("iid", $order_id, $_SESSION['user_id'], $total_amount);
        $stmt->execute();
        
        header('Location: ../order_confirmation.php?order_id=' . $order_id);
        exit;
    }
    
} catch (Exception $e) {
    error_log('Payment processing error: ' . $e->getMessage());
    header('Location: ../checkout.php?error=payment_failed');
    exit;
}
?>
```

### 6.2 Success/Failure Pages
Create `payment/success.php`:
```php
<?php
require_once('../includes/db.php');
require_once('../includes/PayMongoHelper.php');

$payment_id = $_GET['payment_id'] ?? null;

if ($payment_id) {
    try {
        $paymongo = new PayMongoHelper();
        
        // Get payment record
        $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        if ($payment && $payment['paymongo_payment_intent_id']) {
            // Verify payment with PayMongo
            $intent = $paymongo->getPaymentIntent($payment['paymongo_payment_intent_id']);
            
            if ($intent['data']['attributes']['status'] === 'succeeded') {
                // Update payment status
                $stmt = $conn->prepare("UPDATE payments SET status = 'Completed', paid_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $payment_id);
                $stmt->execute();
                
                // Update order status
                $stmt = $conn->prepare("UPDATE orders SET status = 'Processing' WHERE id = ?");
                $stmt->bind_param("i", $payment['order_id']);
                $stmt->execute();
                
                header('Location: ../order_confirmation.php?order_id=' . $payment['order_id']);
                exit;
            }
        }
    } catch (Exception $e) {
        error_log('Payment verification error: ' . $e->getMessage());
    }
}

header('Location: ../checkout.php?error=payment_verification_failed');
?>
```

## 7. Webhook Handler

### 7.1 Create `webhooks/paymongo.php`
```php
<?php
require_once('../includes/db.php');
require_once('../includes/PayMongoHelper.php');

// Get webhook payload
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

// Log webhook for debugging
error_log('PayMongo Webhook: ' . $payload);

try {
    $event = json_decode($payload, true);
    
    if (!$event || !isset($event['data'])) {
        http_response_code(400);
        exit('Invalid payload');
    }
    
    $event_type = $event['data']['attributes']['type'];
    $payment_intent_id = $event['data']['attributes']['data']['id'] ?? null;
    
    // Store webhook
    $stmt = $conn->prepare("INSERT INTO paymongo_webhooks (webhook_id, event_type, payment_intent_id, status, payload) VALUES (?, ?, ?, ?, ?)");
    $webhook_id = $event['data']['id'];
    $status = $event['data']['attributes']['data']['attributes']['status'] ?? 'unknown';
    $stmt->bind_param("sssss", $webhook_id, $event_type, $payment_intent_id, $status, $payload);
    $stmt->execute();
    
    // Process specific events
    if ($event_type === 'payment_intent.payment.paid') {
        // Find payment by intent ID
        $stmt = $conn->prepare("SELECT * FROM payments WHERE paymongo_payment_intent_id = ?");
        $stmt->bind_param("s", $payment_intent_id);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        
        if ($payment) {
            // Update payment status
            $stmt = $conn->prepare("UPDATE payments SET status = 'Completed', paid_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $payment['id']);
            $stmt->execute();
            
            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET status = 'Processing' WHERE id = ?");
            $stmt->bind_param("i", $payment['order_id']);
            $stmt->execute();
        }
    }
    
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    error_log('Webhook processing error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error';
}
?>
```

## 8. Admin Integration

### 8.1 Update admin orders.php
Add payment status and gateway info to orders table.

### 8.2 Create payment management page
`admin/payments.php` for viewing payment transactions.

## 9. Testing

### 9.1 Test Mode Setup
- Use test API keys
- Use test GCash account: `+639123456789`
- Test OTP: `123456`

### 9.2 Test Cases
1. **COD Order**: Normal checkout flow
2. **GCash Payment**: Complete payment flow
3. **Failed Payment**: Handle failures gracefully
4. **Webhook Processing**: Verify status updates

## 10. Production Checklist

- [ ] Get live PayMongo API credentials
- [ ] Update configuration with live keys
- [ ] Set up production webhooks
- [ ] Test with real payments
- [ ] Monitor transaction logs
- [ ] Set up error alerting

## 11. Security Considerations

1. **API Key Security**: Store in environment variables
2. **Webhook Validation**: Verify webhook signatures
3. **SQL Injection**: Use prepared statements
4. **Error Handling**: Don't expose sensitive data
5. **HTTPS**: Ensure SSL for all payment pages

## 12. Fee Structure

- **GCash**: 3.5% + ₱15 fixed fee
- **Cards**: 3.5% + ₱15 fixed fee
- **InstaPay**: 3.5% + ₱10 fixed fee

---

**Implementation Time**: 3-5 days
**Testing Time**: 2-3 days
**Go-Live**: After PayMongo approval