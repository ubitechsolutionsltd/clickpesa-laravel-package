# ClickPesa PHP & Laravel SDK

[![Latest Version](https://img.shields.io/packagist/v/clickpesa/clickpesa-laravel-sdk.svg?style=flat-square)](https://packagist.org/packages/clickpesa/clickpesa-laravel-sdk)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg?style=flat-square)]()
[![PHP](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3-blue.svg?style=flat-square)]()

A modern, robust, and extensible SDK for integrating [ClickPesa](https://docs.clickpesa.com) payment gateway and disbursement APIs into any PHP project, with **first-class Laravel integration** (Service Provider, Facade, Events, and Webhooks).

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
composer require clickpesa/clickpesa-laravel-sdk
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

// --- 4. Send Mobile Money Payout ---
$payout = ClickPesa::mobileMoneyPayouts()->create([
    'amount'         => 5000,
    'currency'       => 'TZS',
    'phoneNumber'    => '255755123456',
    'orderReference' => 'PAYOUT-501',
]);

// --- 5. Check Balances ---
$balances = ClickPesa::balance()->get();
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

#### Mobile Money Payout
```php
$client->mobileMoneyPayouts()->create([
    'amount' => 25000,
    'currency' => 'TZS',
    'orderReference' => 'MNO_PAY_01',
    'phoneNumber' => '255712345678',
]);
```

#### Bank Payout
```php
$client->bankPayouts()->create([
    'amount' => 500000,
    'currency' => 'TZS',
    'orderReference' => 'BANK_PAY_01',
    'accountNumber' => '0150123456700',
    'accountName' => 'Acme Supplies Ltd',
    'bic' => 'CORUTZTZ', // CRDB Bank BIC
]);
```

#### Lipa Namba & TanQR
```php
// Retrieve providers (e.g. M-Pesa 503, Airtel 502)
$providers = $client->lipaNambaPayouts()->providers();

// Pay to Lipa Namba
$client->lipaNambaPayouts()->create([
    'amount' => 10000,
    'currency' => 'TZS',
    'orderReference' => 'LN_PAY_01',
    'lipaNamba' => '48001268',
    'providerCode' => '503',
]);
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

## Security

If you discover any security issues with this package, please contact security@clickpesa.com.

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
