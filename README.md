<p align="center">
  <img src="art/logo.png" alt="UBITECH SOLUTIONS LIMITED" width="450">
</p>

<h1 align="center">ClickPesa PHP & Laravel SDK</h1>

<p align="center">
  <strong>Developed & Maintained by <a href="https://github.com/ubitechsolutionsltd">UBITECH SOLUTIONS LIMITED</a></strong><br>
  📞 <strong>Contact / Support:</strong> <a href="tel:+255766192332">+255 766 192 332</a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/ubitechsolutionsltd/clickpesa-laravel-package"><img src="https://img.shields.io/packagist/v/ubitechsolutionsltd/clickpesa-laravel-package.svg?style=flat-square" alt="Latest Version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
  <img src="https://img.shields.io/badge/tests-passing-brightgreen.svg?style=flat-square" alt="Tests">
  <img src="https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3-blue.svg?style=flat-square" alt="PHP">
  <a href="documentation.pdf"><img src="https://img.shields.io/badge/PDF-Documentation-red.svg?style=flat-square" alt="PDF Documentation"></a>
</p>

A modern, robust, and extensible SDK for integrating [ClickPesa](https://docs.clickpesa.com) payment gateway and disbursement APIs into any PHP project, with **first-class Laravel integration** (Service Provider, Facade, Events, and Webhooks). Includes complete offline reference in [documentation.pdf](documentation.pdf).

---

## Features

- 🌐 **Two-Layer Decoupled Architecture**: Can be used in plain PHP scripts, Symfony, WordPress, or with first-class Laravel integration.
- 🔑 **Automatic JWT Token Management**: Handles `POST /generate-token`, transparent in-memory and Laravel Cache persistence, and proactive refresh before the 1-hour expiration.
- 🛡️ **Built-in Checksum Engine**: Implements ClickPesa's canonicalization algorithm (recursive alphabetical sorting, compact JSON, HMAC-SHA256) for both outgoing request signing and incoming webhook verification.
- 📱 **Complete Payment Collections**:
  - **Mobile USSD-PUSH**: Preview fees and send push requests to M-Pesa, Tigo-Pesa, Airtel Money, Halopesa.
  - **Card Payments**: Preview fees and initiate card payment sessions.
  - **Hosted Checkout Links**: Generate hosted payment URLs with itemized details or order totals.
  - **BillPay (Control Numbers)**: Generate single & bulk control numbers for orders and customers, query details, and update bills.
  - **CRDB Direct Debit**: Request recurring mandate approvals, inspect mandates, and cancel them.
- 💸 **Instant Disbursements (Payouts)**:
  - **Mobile Money (MNO) Payouts**: Send payouts directly to mobile wallets with built-in 60s cooldown detection (`RateLimitException`).
  - **Bank Payouts**: ACH/RTGS transfers with BIC and account verification.
  - **Lipa Namba / TanQR Payouts**: Instant payments to merchant Lipa Namba or TanQR codes.
  - **Payout Links**: Generate hosted disbursement links.
- 📊 **Account & Utilities**:
  - Live balances across currencies (TZS, USD).
  - Account statements.
  - Bank BIC directory.
  - Real-time exchange rates.
- ⚡ **Webhook Processing**:
  - Typed event objects (`PaymentReceivedEvent`, `PaymentFailedEvent`, `PayoutInitiatedEvent`, etc.).
  - Automatic signature verification with timing-attack prevention (`hash_equals`).
  - Native Laravel route macro (`Route::clickpesaWebhooks()`) and dispatched Laravel events.
- 🧪 **Testing Fakes**: `ClickPesa::fake()` and assertion methods for effortless unit and feature testing.

---

## Installation

Install the package via Composer:

```bash
composer require ubitechsolutionsltd/clickpesa-laravel-package
```

---

## Laravel Quickstart

### 1. Configuration & Publishing

In Laravel, the package automatically registers the `ClickPesaServiceProvider` and `ClickPesa` Facade via package discovery.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=clickpesa-config
```

Add your ClickPesa API credentials to your `.env` file:

```env
CLICKPESA_CLIENT_ID=your_client_id_here
CLICKPESA_API_KEY=your_api_key_here
CLICKPESA_CHECKSUM_KEY=your_checksum_secret_here
CLICKPESA_CHECKSUM_ENABLED=true
```

---

### 2. Laravel Usage via Facade

```php
use ClickPesa\Laravel\Facades\ClickPesa;

// --- 1. Initiate Mobile USSD-PUSH ---
$push = ClickPesa::ussdPush()->initiate([
    'amount'         => '10000',
    'currency'       => 'TZS',
    'orderReference' => 'ORDER-1001',
    'phoneNumber'    => '255712345678',
]);

// --- 2. Generate Hosted Checkout Link ---
$checkout = ClickPesa::checkoutLinks()->generate([
    'totalPrice'     => '25000',
    'orderReference' => 'INV-9021',
    'orderCurrency'  => 'TZS',
    'customerName'   => 'Mathayo John',
    'customerEmail'  => 'mathayo@example.com',
    'customerPhone'  => '255712345678',
]);
$redirectUrl = $checkout['checkoutLink'];

// --- 3. Query Payment Status ---
$attempts = ClickPesa::payments()->get('ORDER-1001');

// --- 4. Send Disbursements (All Payout Types Supported) ---

// 4a. Mobile Money Payout (M-Pesa, Airtel, Mixx by Yas, HaloPesa)
$mnoPayout = ClickPesa::mobileMoneyPayouts()->create([
    'amount'         => 25000,
    'currency'       => 'TZS',
    'phoneNumber'    => '255755123456',
    'orderReference' => 'PAYOUT-MNO-01',
]);

// 4b. Bank Payout (EFT / TISS transfers with BIC)
$bankPayout = ClickPesa::bankPayouts()->create([
    'amount'         => 500000,
    'currency'       => 'TZS',
    'orderReference' => 'PAYOUT-BNK-01',
    'accountNumber'  => '0150123456700',
    'accountName'    => 'Acme Ltd',
    'bic'            => 'CORUTZTZ', // CRDB Bank BIC
]);

// 4c. Merchant Lipa Namba Payout
$lnPayout = ClickPesa::lipaNambaPayouts()->create([
    'amount'         => 15000,
    'currency'       => 'TZS',
    'orderReference' => 'PAYOUT-LN-01',
    'lipaNamba'      => '48001268',
    'providerCode'   => '503', // M-Pesa merchant
]);

// 4d. TIPS TanQR Payout (Direct to QR code)
$qrPayout = ClickPesa::lipaNambaPayouts()->create([
    'amount'         => 30000,
    'currency'       => 'TZS',
    'orderReference' => 'PAYOUT-QR-01',
    'qrCode'         => '00020101021226...',
]);

// 4e. Hosted Payout Link (Payee selects preferred destination)
$payoutLink = ClickPesa::payoutLinks()->create([
    'amount'         => 100000,
    'currency'       => 'TZS',
    'orderReference' => 'PAYOUT-LINK-01',
    'recipientName'  => 'Juma Hamisi',
]);
$claimUrl = $payoutLink['payoutUrl'];

// --- 5. Check Balances & Offline Fee Calculation ---
$balances = ClickPesa::balance()->get();
$fee = ClickPesa::fees()->calculateUssdPushFee(15000); // 920 TZS
```

---

### 3. Laravel Webhook Handling

Register the webhook route in your `routes/api.php` or `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

// Registers POST /clickpesa/webhooks
Route::clickpesaWebhooks('clickpesa/webhooks');
```

When ClickPesa sends an event callback, the controller verifies the payload checksum and dispatches the corresponding Laravel event:

| Webhook Event | Dispatched Laravel Event |
| :--- | :--- |
| `PAYMENT RECEIVED` | `ClickPesa\Laravel\Events\PaymentReceived` |
| `PAYMENT FAILED` | `ClickPesa\Laravel\Events\PaymentFailed` |
| `PAYOUT INITIATED` | `ClickPesa\Laravel\Events\PayoutInitiated` |
| `PAYOUT REFUNDED` | `ClickPesa\Laravel\Events\PayoutRefunded` |
| `PAYOUT REVERSED` | `ClickPesa\Laravel\Events\PayoutReversed` |
| `DEPOSIT RECEIVED` | `ClickPesa\Laravel\Events\DepositReceived` |

Listen to events in your `EventServiceProvider` or listeners:

```php
use ClickPesa\Laravel\Events\PaymentReceived;
use Illuminate\Support\Facades\Event;

Event::listen(PaymentReceived::class, function (PaymentReceived $event) {
    $orderRef = $event->getOrderReference();
    $amount = $event->getCollectedAmount();
    $paymentRef = $event->getPaymentReference();

    // Mark order as paid in your database
});
```

---

### 4. Testing with `ClickPesa::fake()`

You can mock all ClickPesa API calls during your tests:

```php
use ClickPesa\Laravel\Facades\ClickPesa;

public function test_user_can_initiate_payment(): void
{
    ClickPesa::fake();

    $this->postJson('/api/checkout', ['amount' => 5000])
         ->assertOk();

    ClickPesa::assertInitiatedUssdPush(function ($data) {
        return $data['amount'] === '5000';
    });

    ClickPesa::assertNotSent('/payouts/create-bank-payout');
}
```

---

## Standalone PHP Usage (Non-Laravel)

You can use the SDK anywhere in PHP without Laravel:

```php
require_once __DIR__ . '/vendor/autoload.php';

use ClickPesa\ClickPesaClient;

$client = new ClickPesaClient([
    'client_id'        => 'YOUR_CLIENT_ID',
    'api_key'          => 'YOUR_API_KEY',
    'checksum_key'     => 'YOUR_CHECKSUM_KEY', // optional
    'checksum_enabled' => true,
]);

// 1. Preview and Initiate USSD Push
$preview = $client->ussdPush()->preview([
    'amount'         => '1000',
    'currency'       => 'TZS',
    'orderReference' => 'REF123',
    'phoneNumber'    => '255712345678',
]);

$response = $client->ussdPush()->initiate([
    'amount'         => '1000',
    'currency'       => 'TZS',
    'orderReference' => 'REF123',
    'phoneNumber'    => '255712345678',
]);

// 2. Create BillPay Order Control Number
$bill = $client->billPay()->createOrder([
    'billDescription' => 'Invoice #1042',
    'billAmount'      => 45000,
    'billReference'   => 'INV1042',
]);
$controlNumber = $bill['billPayNumber'];

// 3. Standalone Webhook Handling
use ClickPesa\Webhooks\WebhookHandler;

$handler = new WebhookHandler('YOUR_CHECKSUM_KEY');
$event = $handler->handle(file_get_contents('php://input'));

if ($event instanceof \ClickPesa\Webhooks\Events\PaymentReceivedEvent) {
    echo "Payment received: " . $event->getCollectedAmount();
}
```

---

## Complete API Guide

### 1. Collections (Payments)

#### USSD Push
```php
// Preview available telco methods & fees
$client->ussdPush()->preview([
    'amount' => '5000',
    'currency' => 'TZS',
    'orderReference' => 'ORD101',
    'phoneNumber' => '255712345678',
]);

// Send USSD-Push prompt
$client->ussdPush()->initiate([
    'amount' => '5000',
    'currency' => 'TZS',
    'orderReference' => 'ORD101',
    'phoneNumber' => '255712345678',
]);
```

#### Card Payments
```php
$client->cardPayments()->initiate([
    'amount' => '50',
    'currency' => 'USD',
    'orderReference' => 'CARD_01',
    'customer' => [
        'fullName' => 'John Doe',
        'email' => 'john@example.com',
        'phoneNumber' => '255712345678',
    ],
]);
```

#### BillPay Control Numbers
```php
// Create one-time Order Control Number
$client->billPay()->createOrder([
    'billDescription' => 'School Fees',
    'billAmount' => 150000,
    'billReference' => 'SCH901',
]);

// Create Customer Control Number
$client->billPay()->createCustomer();

// Bulk creation (up to 50 items)
$client->billPay()->bulkCreateOrders([
    ['billDescription' => 'Bill 1', 'billAmount' => 5000],
    ['billDescription' => 'Bill 2', 'billAmount' => 10000],
]);

// Query details & Update bill
$client->billPay()->get('55042914871931');
$client->billPay()->update('55042914871931', ['billAmount' => 180000]);
```

#### Payment Status
```php
// Query by Order Reference (returns array of attempts)
$attempts = $client->payments()->get('ORDER-101');

// Query all payments with filters
$list = $client->payments()->all([
    'status' => 'SUCCESS',
    'startDate' => '2026-01-01',
    'limit' => 20,
]);
```

---

### 2. Disbursements (Payouts)

ClickPesa supports 5 disbursement channels. All payout endpoints are subject to a **60-second cooldown** between payout creations (`RateLimitException`).

#### 2a. Mobile Money Payout (M-Pesa, Airtel, Mixx by Yas, HaloPesa)
```php
// Step 1: Preview fee and check balance before sending
$preview = $client->mobileMoneyPayouts()->preview([
    'amount'         => 25000,
    'currency'       => 'TZS',
    'phoneNumber'    => '255755123456',
    'orderReference' => 'MNO_PAY_01',
]);
$fee = $preview['fee']; // e.g., 700 TZS

// Step 2: Create mobile money payout
$payout = $client->mobileMoneyPayouts()->create([
    'amount'         => 25000,
    'currency'       => 'TZS',
    'phoneNumber'    => '255755123456',
    'orderReference' => 'MNO_PAY_01',
]);
```

#### 2b. Bank Payout (EFT / ACH vs TISS)
```php
// Step 1: Preview bank payout (verifies BIC and estimated arrival)
$preview = $client->bankPayouts()->preview([
    'amount'        => 500000,
    'currency'      => 'TZS',
    'accountNumber' => '0150123456700',
    'bic'           => 'CORUTZTZ', // CRDB Bank BIC
    'orderReference'=> 'BANK_PAY_01',
]);

// Step 2: Create bank payout
// Note: Transfers <= 20M TZS use EFT (fee: 2,360 TZS). Transfers > 20M TZS must use TISS (fee: 11,800 TZS).
$payout = $client->bankPayouts()->create([
    'amount'        => 500000,
    'currency'      => 'TZS',
    'orderReference'=> 'BANK_PAY_01',
    'accountNumber' => '0150123456700',
    'accountName'   => 'Acme Supplies Ltd',
    'bic'           => 'CORUTZTZ',
    'transferType'  => 'ACH', // 'ACH' for EFT, 'RTGS' for TISS
]);
```

#### 2c. Merchant Lipa Namba Payout
```php
// Retrieve active Lipa Namba providers (e.g., M-Pesa 503, Airtel 502)
$providers = $client->lipaNambaPayouts()->providers();

// Preview and create payout to merchant Lipa Namba
$payout = $client->lipaNambaPayouts()->create([
    'amount'         => 15000,
    'currency'       => 'TZS',
    'orderReference' => 'LN_PAY_01',
    'lipaNamba'      => '48001268',
    'providerCode'   => '503',
]);
```

#### 2d. TIPS TanQR Payout (Direct to QR)
```php
// Payout directly using the merchant's TIPS TanQR code
$qrPayout = $client->lipaNambaPayouts()->create([
    'amount'         => 30000,
    'currency'       => 'TZS',
    'orderReference' => 'QR_PAY_01',
    'qrCode'         => '00020101021226...',
]);
```

#### 2e. Hosted Payout Link
```php
// Generate a hosted payout link where the recipient selects their preferred wallet or bank
$link = $client->payoutLinks()->create([
    'amount'         => 50000,
    'currency'       => 'TZS',
    'orderReference' => 'LINK_PAY_01',
    'recipientName'  => 'Baraka Mushi',
]);
$claimUrl = $link['payoutUrl'];
```

#### 2f. Querying Payout Status & Tracking
```php
// Get payout status by Order Reference
$status = $client->payouts()->get('MNO_PAY_01');

// Filter & paginate payouts history
$history = $client->payouts()->all([
    'status'    => 'SUCCESS',
    'startDate' => '2026-09-01',
    'limit'     => 50,
]);
```

#### 2g. Handling the 60-Second Cooldown
ClickPesa enforces a 60-second cooldown between payout creations:
```php
use ClickPesa\Exceptions\RateLimitException;

try {
    $payout = $client->mobileMoneyPayouts()->create([...]);
} catch (RateLimitException $e) {
    // Cooldown active - wait or queue the next payout job
    logger()->warning('ClickPesa payout rate limited: ' . $e->getMessage());
}
```

---

### 3. Pricing, Fee Bearers & Offline Fee Calculator

ClickPesa charges different fees depending on the channel and whether the customer or the merchant bears the cost. The SDK provides an instant, offline **`FeeCalculator`** so you can estimate and display fees in checkout screens without waiting for API roundtrips.

#### Fee Bearers Overview

| Channel | Fee Type | Fee Bearer | Notes |
| :--- | :--- | :--- | :--- |
| **USSD Push** (M-Pesa, Airtel, Mixx by Yas, HaloPesa) | Tiered slab (54 – 7,960 TZS) | **Customer** | Added to the customer's payment at checkout. |
| **Card Payments** (Visa, Mastercard, UnionPay) | **4.85%** | **Customer** | Added to the customer's payment at checkout. |
| **BillPay** (M-Pesa & Airtel) | **1.0%** | **Merchant** | Deducted from merchant settlement. |
| **BillPay** (HaloPesa) | **2.0%** | **Merchant** | Deducted from merchant settlement. |
| **BillPay** (Mixx by Yas / Tigo) | **2.5%** | **Merchant** | Deducted from merchant settlement. |
| **BillPay** (CRDB) | **1.0%** | **Merchant** | Deducted from merchant settlement. |
| **CRDB Direct Debit** | **2,000 TZS** flat | **Merchant** | Charged per successful scheduled debit. |
| **TIPS TanQR Collection** | **2.0%** | **Merchant** | Deducted from merchant settlement. |
| **Mobile Money & TanQR Payouts** | Tiered slab (52 – 9,890 TZS) | **Configurable** | Merchant can absorb fee OR deduct from payee. |
| **Bank EFT Payout** | **2,360 TZS** flat | **Configurable** | For transfers $\le$ 20,000,000 TZS. |
| **Bank TISS Payout (TZS)** | **11,800 TZS** flat | **Configurable** | For transfers up to 1,000,000,000 TZS. |
| **Bank TISS Payout (USD)** | **$7.50 USD** flat | **Configurable** | For transfers up to $1,000,000 USD. |

#### Offline Fee Calculation Examples
```php
use ClickPesa\Laravel\Facades\ClickPesa;
use ClickPesa\Pricing\ChannelLimits;

// 1. Calculate USSD Push fee
$ussdFee = ClickPesa::fees()->calculateUssdPushFee(15000); // 920.0 TZS

// 2. Calculate Card Payment fee
$cardFee = ClickPesa::fees()->calculateCardFee(50000); // 2,425.0 TZS (4.85%)

// 3. Calculate BillPay fee
$mpesaFee = ClickPesa::fees()->calculateBillPayFee('mpesa', 100000); // 1,000.0 TZS (1%)
$mixxFee  = ClickPesa::fees()->calculateBillPayFee('mixx', 100000);  // 2,500.0 TZS (2.5%)

// 4. Calculate Net Merchant Settlement (e.g. on 100,000 TZS collection)
$net = ClickPesa::fees()->calculateNetSettlement('billpay_mpesa', 100000); // 99,000.0 TZS

// 5. Payout Budgeting (Absorb Fee vs Deduct from Recipient)
// Option A: Business absorbs fee (payee receives full 50,000 TZS; business wallet debited 51,460 TZS)
$budgetA = ClickPesa::fees()->calculatePayoutDeduction('mobile_money', 50000, absorbFee: true);

// Option B: Deduct fee from payee (business wallet debited 50,000 TZS; payee receives 48,540 TZS)
$budgetB = ClickPesa::fees()->calculatePayoutDeduction('mobile_money', 50000, absorbFee: false);

// 6. Pre-Flight Limit Validation (Prevents 400 Bad Request)
ChannelLimits::validate('ussd_push', 15000); // Passes
// ChannelLimits::validate('ussd_push', 300); // Throws ValidationException: below minimum 500 TZS!

// 7. Smart Bank Transfer Method Recommender
$method = ChannelLimits::recommendBankTransferMethod(25000000); // Returns 'TISS' (> 20M TZS)
```

#### Environment Variables for Custom Pricing
All rates can be overridden in your `.env` file if ClickPesa updates fees or if custom negotiated rates apply to your account:

```env
# Card percentage fee (default: 4.85)
CLICKPESA_FEE_CARD_PERCENT=4.85

# BillPay percentage fees
CLICKPESA_FEE_BILLPAY_MPESA_PERCENT=1.0
CLICKPESA_FEE_BILLPAY_AIRTEL_PERCENT=1.0
CLICKPESA_FEE_BILLPAY_HALOPESA_PERCENT=2.0
CLICKPESA_FEE_BILLPAY_MIXX_PERCENT=2.5
CLICKPESA_FEE_BILLPAY_CRDB_PERCENT=1.0

# Flat fees in TZS
CLICKPESA_FEE_CRDB_DIRECT_DEBIT=2000
CLICKPESA_FEE_BANK_EFT=2360
CLICKPESA_FEE_BANK_TISS_TZS=11800
CLICKPESA_FEE_BANK_TISS_USD=7.50

# Bank EFT threshold (transfers above this use TISS)
CLICKPESA_BANK_EFT_MAX_THRESHOLD=20000000
```

---

### 3. Checksum Verification

To generate or verify checksums directly:

```php
use ClickPesa\Security\Checksum;

// Generate
$checksum = Checksum::generate($secretKey, $payload);

// Timing-safe verification
$isValid = Checksum::verify($secretKey, $payload, $receivedChecksum);
```

---

## Exception Handling

All exceptions extend `ClickPesa\Exceptions\ClickPesaException`:

```php
use ClickPesa\Exceptions\AuthenticationException;
use ClickPesa\Exceptions\ValidationException;
use ClickPesa\Exceptions\RateLimitException;
use ClickPesa\Exceptions\ConflictException;
use ClickPesa\Exceptions\NotFoundException;

try {
    ClickPesa::mobileMoneyPayouts()->create([...]);
} catch (RateLimitException $e) {
    // 60-second payout cooldown
    echo "Retry after: " . $e->getRetryAfterSeconds() . " seconds";
} catch (ValidationException $e) {
    echo "Invalid data: " . $e->getMessage();
} catch (ConflictException $e) {
    echo "Order reference already used: " . $e->getMessage();
} catch (AuthenticationException $e) {
    echo "Auth error: " . $e->getMessage();
}
```

---

## Testing

Run the test suite using PHPUnit:

```bash
composer test
```

Or directly:

```bash
vendor/bin/phpunit
```

---

## Authors & Support

This SDK is developed and maintained by **UBITECH SOLUTIONS LIMITED**.

- **Organization**: UBITECH SOLUTIONS LIMITED
- **GitHub**: [github.com/ubitechsolutionsltd](https://github.com/ubitechsolutionsltd)
- **Phone / Support**: [+255 766 192 332](tel:+255766192332)
- **Offline PDF Manual**: [documentation.pdf](documentation.pdf)

---

## Security

If you discover any security issues with this package, please contact security@clickpesa.com or reach out to UBITECH SOLUTIONS (+255 766 192 332).

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
