# ClickPesa PHP & Laravel SDK Implementation Plan

A modern, robust, and extensible SDK for integrating [ClickPesa](https://docs.clickpesa.com) APIs in any PHP project, with first-class Laravel integration (Service Provider, Facade, Events, and Webhooks).

---

## 1. Project Overview & Architecture Principles

### Key Objectives
1. **Universal Reusability**: Standalone core (`ClickPesa\ClickPesaClient`) that can be used in plain PHP, Symfony, WordPress, CLI scripts, or any framework without framework coupling.
2. **First-Class Laravel Integration**:
   - Auto-discovered `ClickPesaServiceProvider` and `ClickPesa` Facade.
   - Config file published to `config/clickpesa.php`.
   - Native integration with Laravel's Cache for JWT token persistence.
   - Automated webhook route registration, signature verification middleware, and Laravel Event dispatching.
   - Testing/Mocking utility (`ClickPesa::fake()`).
3. **Automated Token Management**:
   - ClickPesa JWT tokens are valid for 1 hour.
   - The SDK handles fetching, caching, and proactive renewal (with TTL margin) transparently.
4. **Built-in Checksum Engine**:
   - Exact compliance with ClickPesa's canonicalization algorithm (recursive alphabetical key sorting, compact JSON, HMAC-SHA256).
   - Automatically signs outgoing requests when `checksum_key` is provided or enabled.
   - Provides safe timing-attack resistant signature verification (`hash_equals`) for incoming webhooks.
5. **Strict Typing & Modern PHP**:
   - PHP 8.1+ (supports PHP 8.2 & 8.3).
   - Typed DTOs / Requests / Responses for IDE autocomplete and compile-time confidence.
   - Custom exceptions hierarchy for validation, auth, conflict, and network errors.

---

## 2. Directory & Package Structure

```
clickpesa-laravel-sdk/
├── src/
│   ├── ClickPesaClient.php              # Core SDK entrypoint (framework-agnostic)
│   ├── Config.php                       # SDK configuration container
│   ├── Auth/
│   │   ├── TokenManager.php             # Handles /generate-token, caching & refresh
│   │   ├── TokenCacheInterface.php      # Cache abstraction (in-memory, PSR-16, Laravel)
│   │   ├── InMemoryTokenCache.php       # Default in-memory cache
│   │   └── LaravelTokenCache.php        # Laravel Cache adapter
│   ├── Security/
│   │   ├── Checksum.php                 # Canonicalization & HMAC-SHA256 generation/verification
│   │   └── ChecksumMiddleware.php       # HTTP middleware / request injector
│   ├── Http/
│   │   ├── HttpClientInterface.php      # HTTP transport interface
│   │   ├── GuzzleHttpClient.php         # Default Guzzle implementation
│   │   ├── Request.php                  # Internal request abstraction
│   │   └── Response.php                 # Internal response abstraction
│   ├── Exceptions/
│   │   ├── ClickPesaException.php       # Base exception
│   │   ├── AuthenticationException.php  # 401 Unauthorized / token expired
│   │   ├── ValidationException.php      # 400 Bad request / invalid input
│   │   ├── NotFoundException.php        # 404 Resource not found
│   │   ├── ConflictException.php        # 409 Order reference conflict
│   │   ├── RateLimitException.php       # Payout 60s cooldown limit
│   │   └── WebhookVerificationException.php # Invalid checksum / signature
│   ├── Resources/
│   │   ├── AbstractResource.php         # Base resource with HTTP helper methods
│   │   ├── Collection/
│   │   │   ├── UssdPushResource.php     # Preview & Initiate USSD-PUSH
│   │   │   ├── CardPaymentResource.php  # Preview & Initiate Card Payment
│   │   │   ├── CheckoutLinkResource.php # Generate Hosted Checkout Link
│   │   │   ├── BillPayResource.php      # Order/Customer Control Numbers & Bulk
│   │   │   ├── CrdbDirectDebitResource.php # Mandates & Payment Plans
│   │   │   └── PaymentStatusResource.php   # Query /payments/{orderRef} & /payments/all
│   │   ├── Disbursement/
│   │   │   ├── MobileMoneyPayoutResource.php # Preview & Create MNO Payouts
│   │   │   ├── BankPayoutResource.php        # Preview & Create Bank Payouts
│   │   │   ├── LipaNambaPayoutResource.php   # Providers, Preview & Create Lipa Namba Payouts
│   │   │   ├── PayoutLinkResource.php        # Generate Payout Link
│   │   │   └── PayoutStatusResource.php      # Query /payouts/{orderRef} & /payouts/all
│   │   └── Account/
│   │       ├── BalanceResource.php      # /account/balance & /account/statement
│   │       ├── BankListResource.php     # /list/banks
│   │       └── ExchangeRateResource.php # /exchange-rates/all
│   ├── Webhooks/
│   │   ├── WebhookHandler.php           # Payload parsing, checksum verification & routing
│   │   └── Events/                      # Webhook DTOs
│   │       ├── PaymentReceivedEvent.php
│   │       ├── PaymentFailedEvent.php
│   │       ├── PayoutInitiatedEvent.php
│   │       ├── PayoutRefundedEvent.php
│   │       ├── PayoutReversedEvent.php
│   │       └── DepositReceivedEvent.php
│   └── Laravel/
│       ├── ClickPesaServiceProvider.php # Service provider (register & boot)
│       ├── Facades/
│       │   └── ClickPesa.php            # Static facade for Laravel
│       ├── Http/
│       │   ├── Controllers/
│       │   │   └── ClickPesaWebhookController.php
│       │   └── Middleware/
│       │       └── VerifyClickPesaWebhookSignature.php
│       ├── Events/                      # Native Laravel dispatchable events
│       │   ├── PaymentReceived.php
│       │   ├── PaymentFailed.php
│       │   ├── PayoutInitiated.php
│       │   ├── PayoutRefunded.php
│       │   ├── PayoutReversed.php
│       │   └── DepositReceived.php
│       └── Testing/
│           └── ClickPesaFake.php        # Mocking container for tests
├── config/
│   └── clickpesa.php                    # Default Laravel config
├── tests/
│   ├── Unit/
│   │   ├── ChecksumTest.php
│   │   ├── TokenManagerTest.php
│   │   ├── UssdPushResourceTest.php
│   │   ├── BillPayResourceTest.php
│   │   ├── PayoutResourceTest.php
│   │   └── WebhookHandlerTest.php
│   ├── Feature/
│   │   ├── LaravelServiceProviderTest.php
│   │   ├── LaravelWebhookControllerTest.php
│   │   └── ClickPesaFacadeTest.php
│   └── TestCase.php
├── composer.json
├── phpunit.xml.dist
├── README.md
├── LICENSE
└── plan.md
```

---

## 3. Detailed API & Feature Coverage

### 3.1 Authorization & Token Management (`POST /generate-token`)
- **Credentials**: `client-id`, `api-key`.
- **Base URL**: `https://api.clickpesa.com/third-parties`.
- **Token Format**: JWT string returned in response `{ success: true, token: "Bearer ey..." }`.
- **Auto-caching**:
  - Valid for 1 hour.
  - Cached with 5-minute safety buffer (refreshes after 55 minutes).
  - In Laravel: stores in `Cache::store()` tag/key `clickpesa_auth_token`.
  - In standalone PHP: `InMemoryTokenCache` or custom PSR-16 cache implementation.

### 3.2 Payload Checksum Engine (HMAC-SHA256)
- Implements ClickPesa canonicalization:
  - Recursive key sorting (`ksort`) for associative arrays/objects.
  - Retains indexed array order while canonicalizing nested values.
  - Strict compact JSON serialization (`json_encode($data, JSON_UNESCAPED_SLASHES)`).
  - Generates 64-char HMAC-SHA256 hex string with `checksumKey`.
- Automatically strips `checksum` and `checksumMethod` before calculation.
- Supports both:
  - Request signing: Attaches `checksum` to payload when `checksum_enabled` is true.
  - Webhook verification: Validates incoming request signature using `hash_equals`.

### 3.3 Collections (Payment Acceptance)
1. **Mobile USSD Push**:
   - `previewUssdPush(array $params)`: `POST /payments/preview-ussd-push-request`
     - Validates amount, currency (`TZS`), orderReference (max 20 chars), phoneNumber (`255XXXXXXXXX`), and optionally fetches sender details.
   - `initiateUssdPush(array $params)`: `POST /payments/initiate-ussd-push-request`
     - Triggers push prompt to customer handset.
2. **Card Payment**:
   - `previewCardPayment(array $params)`: `POST /payments/preview-card-payment`
   - `initiateCardPayment(array $params)`: `POST /payments/initiate-card-payment`
     - Supports either customer ID or customer details (fullName, email, phoneNumber). Returns `cardPaymentLink`.
3. **Hosted Checkout**:
   - `generateCheckoutUrl(array $params)`: `POST /checkout-link/generate-checkout-url`
     - Supports total price or order items list, redirect URL, callback URL.
4. **BillPay (Control Numbers)**:
   - `createOrderControlNumber(array $params)`: `POST /billpay/create-order-control-number`
   - `createCustomerControlNumber(array $params)`: `POST /billpay/create-customer-control-number`
   - `bulkCreateOrderControlNumbers(array $items)`: `POST /billpay/bulk-create-order-control-numbers` (up to 50 items)
   - `bulkCreateCustomerControlNumbers(array $items)`: `POST /billpay/bulk-create-customer-control-numbers` (up to 50 items)
   - `getBillPayDetails(string $billPayNumber)`: `GET /billpay/{billPayNumber}`
   - `updateBillPay(string $billPayNumber, array $params)`: `PATCH /billpay/{billPayNumber}`
5. **CRDB Direct Debit**:
   - `requestMandate(array $params)`: `POST /crdb-direct-debit/request-mandate`
   - `getMandate(string $mandateRequestId)`: `GET /crdb-direct-debit/get-mandate/{mandateRequestId}`
   - `cancelMandate(array $params)`: `POST /crdb-direct-debit/cancel-mandate`
6. **Payment Query**:
   - `getPayment(string $orderReference)`: `GET /payments/{orderReference}` (returns array of payment attempts)
   - `getAllPayments(array $query = [])`: `GET /payments/all` (filters: `startDate`, `endDate`, `status`, `currency`, `channel`, `skip`, `limit`, `sortBy`, `orderBy`)

### 3.4 Disbursements (Payouts)
1. **Mobile Money Payout**:
   - `previewMobileMoneyPayout(array $params)`: `POST /payouts/preview-mobile-money-payout`
   - `createMobileMoneyPayout(array $params)`: `POST /payouts/create-mobile-money-payout` (enforces 60s rate limit handling)
2. **Bank Payout**:
   - `previewBankPayout(array $params)`: `POST /payouts/preview-bank-payout`
   - `createBankPayout(array $params)`: `POST /payouts/create-bank-payout` (accountNumber, bic, currency, etc.)
3. **Lipa Namba Payout**:
   - `getLipaNambaProviders()`: `GET /payouts/lipa-namba-providers`
   - `previewLipaNambaPayout(array $params)`: `POST /payouts/preview-lipa-namba-payout`
   - `createLipaNambaPayout(array $params)`: `POST /payouts/create-lipa-namba-payout` (lipaNamba + providerCode OR qrCode)
4. **Payout Query & Links**:
   - `getPayout(string $orderReference)`: `GET /payouts/{orderReference}`
   - `getAllPayouts(array $query = [])`: `GET /payouts/all`
   - `generatePayoutUrl(array $params)`: `POST /payout-link/generate-payout-url`

### 3.5 Account, Banks & Exchange Rates
- `getBalance()`: `GET /account/balance` (handles initial 404 when no transactions yet)
- `getStatement(array $query = [])`: `GET /account/statement`
- `getBanks()`: `GET /list/banks`
- `getExchangeRates(array $query = [])`: `GET /exchange-rates/all`

### 3.6 Webhook Handling
- ClickPesa sends HTTP POST with JSON body containing event and data.
- Events supported:
  - `PAYMENT RECEIVED`
  - `PAYMENT FAILED`
  - `PAYOUT INITIATED`
  - `PAYOUT REFUNDED`
  - `PAYOUT REVERSED`
  - `DEPOSIT RECEIVED`
- Laravel Webhook Controller with route helper `Route::clickpesaWebhooks('clickpesa/webhooks')`.
- Dispatches typed Laravel events for each webhook type so developers simply register standard Laravel Listeners.

---

## 4. Usage Examples

### 4.1 Standalone PHP Usage
```php
use ClickPesa\ClickPesaClient;

$client = new ClickPesaClient([
    'client_id'        => 'YOUR_CLIENT_ID',
    'api_key'          => 'YOUR_API_KEY',
    'checksum_key'     => 'YOUR_CHECKSUM_KEY', // optional
    'checksum_enabled' => true,
]);

// 1. Initiate USSD Push
$response = $client->ussdPush()->initiate([
    'amount'         => '5000',
    'currency'       => 'TZS',
    'orderReference' => 'ORD123456',
    'phoneNumber'    => '255712345678',
]);

// 2. Query Payment
$status = $client->payments()->get('ORD123456');

// 3. Create Bank Payout
$payout = $client->bankPayouts()->create([
    'amount'         => 100000,
    'accountNumber'  => '0150123456700',
    'accountName'    => 'Jane Doe',
    'currency'       => 'TZS',
    'orderReference' => 'PAY123456',
    'bic'            => 'CRDBTZTZ',
]);
```

### 4.2 Laravel Usage
```php
use ClickPesa\Laravel\Facades\ClickPesa;

// Initiate USSD push
$response = ClickPesa::ussdPush()->initiate([
    'amount'         => '10000',
    'currency'       => 'TZS',
    'orderReference' => 'INV-9921',
    'phoneNumber'    => '255700000000',
]);

// Generate Hosted Checkout Link
$link = ClickPesa::checkoutLink()->generate([
    'totalPrice'     => '25000',
    'orderReference' => 'INV-9922',
    'orderCurrency'  => 'TZS',
    'customerName'   => 'Mathayo John',
    'customerEmail'  => 'mathayo@example.com',
]);
```

### 4.3 Webhook Handling in Laravel
In `routes/api.php` or `routes/web.php`:
```php
Route::clickpesaWebhooks('webhooks/clickpesa');
```

In `EventServiceProvider.php` (or event listeners):
```php
use ClickPesa\Laravel\Events\PaymentReceived;

Event::listen(PaymentReceived::class, function (PaymentReceived $event) {
    $orderRef = $event->orderReference;
    $amount = $event->collectedAmount;
    // Mark order as paid in database
});
```

---

## 5. Development & Testing Plan

1. **Unit Tests (Pest or PHPUnit)**:
   - Checksum canonicalization matching ClickPesa's specification with edge cases (nested objects, lists, numbers, nulls, special characters).
   - Token Manager caching, refresh window, and handling 401/403 credentials error.
   - Resource request formatting, headers, query parameters, error mapping.
   - Webhook verification and payload deserialization.
2. **Feature / Integration Tests with Mock Responses**:
   - Using Guzzle MockHandler / Mock Client to verify end-to-end API calls for each resource.
   - Laravel ServiceProvider registration, config loading, Facade resolution, and webhook route testing via `orchestra/testbench`.
3. **Static Analysis & Linting**:
   - PHPStan (Level 8 or max).
   - Laravel Pint for PSR-12 code style formatting.
4. **Documentation**:
   - Complete `README.md` with installation, setup, configuration, standalone usage, Laravel usage, webhook configuration, and testing guidance.

---

## 6. Implementation Phases

- **Phase 1**: Setup project skeleton (`composer.json`, `phpunit.xml.dist`, dependencies including Guzzle, Orchestra Testbench, Pest/PHPUnit).
- **Phase 2**: Security & Core Infrastructure (Canonicalizer, Checksum, TokenManager, Cache interfaces, Guzzle HTTP transport, Exceptions).
- **Phase 3**: Core Resources (USSD Push, Card, Checkout Links, BillPay, CRDB Direct Debit, Payments, MNO Payouts, Bank Payouts, Lipa Namba, Account & Lists).
- **Phase 4**: Webhook Engine (Payload parser, DTOs, verification).
- **Phase 5**: Laravel Integration (ServiceProvider, Facades, Config, Webhook Controller, Events, Route Macro, Testing Fake).
- **Phase 6**: Unit & Feature Test Suite (100% core coverage).
- **Phase 7**: Comprehensive README, Examples, and final verification.
