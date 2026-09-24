import os
import base64
import subprocess

def create_documentation_pdf():
    project_dir = r"d:\projects\clickpesa-laravel-sdk"
    logo_path = os.path.join(project_dir, "art", "logo.png")
    output_html = os.path.join(project_dir, "documentation.html")
    output_pdf = os.path.join(project_dir, "documentation.pdf")

    # Read and encode logo to base64
    with open(logo_path, "rb") as img_file:
        logo_base64 = base64.b64encode(img_file.read()).decode('utf-8')

    html_content = f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ClickPesa PHP & Laravel SDK Documentation - UBITECH SOLUTIONS LIMITED</title>
<style>
  @page {{
    size: A4;
    margin: 20mm 15mm 20mm 15mm;
    @bottom-right {{
      content: counter(page);
    }}
  }}

  body {{
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e293b;
    line-height: 1.6;
    font-size: 13px;
    margin: 0;
    padding: 0;
  }}

  .cover-page {{
    page-break-after: always;
    height: 90vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: center;
    padding-top: 50px;
  }}

  .cover-logo {{
    max-width: 420px;
    margin: 0 auto 30px auto;
  }}

  .cover-title {{
    font-size: 32px;
    font-weight: 800;
    color: #0284c7;
    margin: 10px 0;
    letter-spacing: -0.5px;
  }}

  .cover-subtitle {{
    font-size: 18px;
    color: #475569;
    margin-bottom: 40px;
    font-weight: 500;
  }}

  .cover-badge {{
    display: inline-block;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 20px;
    margin-bottom: 30px;
  }}

  .cover-author-box {{
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    max-width: 480px;
    margin: 0 auto;
    text-align: center;
  }}

  .cover-author-box h3 {{
    margin: 0 0 10px 0;
    color: #0f172a;
    font-size: 16px;
  }}

  .cover-author-box p {{
    margin: 4px 0;
    color: #64748b;
    font-size: 13px;
  }}

  .cover-author-box .phone {{
    color: #0284c7;
    font-weight: 700;
    font-size: 15px;
  }}

  .page-break {{
    page-break-before: always;
  }}

  h1 {{
    color: #0f172a;
    font-size: 22px;
    border-bottom: 2px solid #0284c7;
    padding-bottom: 6px;
    margin-top: 30px;
    page-break-after: avoid;
  }}

  h2 {{
    color: #1e293b;
    font-size: 17px;
    margin-top: 24px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 4px;
    page-break-after: avoid;
  }}

  h3 {{
    color: #334155;
    font-size: 14px;
    margin-top: 18px;
    page-break-after: avoid;
  }}

  p, li {{
    color: #334155;
    font-size: 13px;
  }}

  table {{
    width: 100%;
    border-collapse: collapse;
    margin: 16px 0;
    font-size: 12px;
    page-break-inside: avoid;
  }}

  th, td {{
    border: 1px solid #cbd5e1;
    padding: 8px 10px;
    text-align: left;
  }}

  th {{
    background: #f1f5f9;
    color: #0f172a;
    font-weight: 700;
  }}

  tr:nth-child(even) {{
    background: #f8fafc;
  }}

  pre, code {{
    font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
  }}

  code {{
    background: #f1f5f9;
    color: #0369a1;
    padding: 2px 5px;
    border-radius: 4px;
    font-size: 12px;
  }}

  pre {{
    background: #0f172a;
    color: #f8fafc;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 11.5px;
    overflow-x: auto;
    page-break-inside: avoid;
    line-height: 1.45;
  }}

  pre code {{
    background: transparent;
    color: #f8fafc;
    padding: 0;
  }}

  .alert {{
    border-left: 4px solid #0284c7;
    background: #f0f9ff;
    padding: 10px 14px;
    border-radius: 4px;
    margin: 14px 0;
    font-size: 12.5px;
    page-break-inside: avoid;
  }}

  .alert-warning {{
    border-left-color: #f59e0b;
    background: #fffbeb;
  }}

  .alert strong {{
    color: #0369a1;
  }}

  .alert-warning strong {{
    color: #b45309;
  }}

  .header-logo {{
    float: right;
    max-height: 35px;
    margin-top: -10px;
  }}

  .toc-item {{
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px dotted #cbd5e1;
  }}
</style>
</head>
<body>

<!-- COVER PAGE -->
<div class="cover-page">
  <div>
    <img src="data:image/png;base64,{logo_base64}" alt="UBITECH SOLUTIONS LIMITED" class="cover-logo">
    <div class="cover-badge">OFFICIAL DEVELOPER REFERENCE</div>
    <div class="cover-title">ClickPesa PHP & Laravel SDK</div>
    <div class="cover-subtitle">Complete Integration Guide & API Reference Manual</div>
  </div>

  <div class="cover-author-box">
    <h3>UBITECH SOLUTIONS LIMITED</h3>
    <p>Professional Software Engineering & Digital Payment Integrations</p>
    <p class="phone">📞 Phone / Support: +255 766 192 332</p>
    <p>Repository: github.com/ubitechsolutionsltd/clickpesa-laravel-package</p>
    <p style="margin-top: 10px; font-weight: 600; color: #475569;">Release Version: 1.0.1 &bull; September 2026</p>
  </div>
</div>

<!-- SECTION 1: ARCHITECTURE -->
<div class="page-break"></div>

<h1>1. Executive Summary & Architecture</h1>
<p>
  The <strong>ClickPesa PHP & Laravel SDK</strong> is an enterprise-grade library engineered by 
  <strong>UBITECH SOLUTIONS LIMITED</strong> to provide frictionless integration with ClickPesa's 
  collection and disbursement APIs across Tanzania and East Africa.
</p>

<h3>Core Principles</h3>
<ul>
  <li><strong>Universal Compatibility (Any PHP Application)</strong>: Standalone core with zero framework lock-in. Compatible with PHP 8.1, 8.2, and 8.3 in vanilla PHP, Symfony, WordPress, or CLI tools.</li>
  <li><strong>First-Class Laravel Integration</strong>: Out-of-the-box auto-discovery, publishable configuration, static <code>ClickPesa</code> Facade, native Cache token persistence, Webhook routing macro, and dispatched Laravel events.</li>
  <li><strong>Zero-Maintenance Authentication</strong>: Automatic token management that handles issuance, in-memory/cache storage, and preemptive refresh before the 1-hour expiration limit.</li>
  <li><strong>Compliant Checksum Engine</strong>: Built-in canonicalization and HMAC-SHA256 signing for outgoing requests and timing-safe webhook verification.</li>
  <li><strong>Built-in Testing Mock</strong>: <code>ClickPesa::fake()</code> and assertion helpers for instant unit and integration testing without reaching production endpoints.</li>
</ul>

<div class="alert">
  <strong>Note on ClickPesa Environment:</strong> ClickPesa operates directly against live production with small amounts and pre-KYC tier limits. There is no separate sandbox domain; all calls target <code>https://api.clickpesa.com/third-parties</code>.
</div>

<!-- SECTION 2: INSTALLATION -->
<h1>2. Installation & Configuration</h1>

<h3>Installing with Composer</h3>
<pre><code>composer require ubitechsolutionsltd/clickpesa-laravel-package</code></pre>

<h3>Publishing Laravel Configuration</h3>
<pre><code>php artisan vendor:publish --tag=clickpesa-config</code></pre>

<p>This generates <code>config/clickpesa.php</code>. Set your credentials in your <code>.env</code> file:</p>

<pre><code># .env
CLICKPESA_CLIENT_ID=your_client_id_here
CLICKPESA_API_KEY=your_api_key_here
CLICKPESA_CHECKSUM_KEY=your_checksum_secret_here
CLICKPESA_CHECKSUM_ENABLED=true
CLICKPESA_TIMEOUT=30
CLICKPESA_TOKEN_TTL_MARGIN=300</code></pre>

<!-- SECTION 3: AUTHENTICATION & CHECKSUM -->
<div class="page-break"></div>

<h1>3. Authentication & Security Engine</h1>

<h2>Automatic Token Management</h2>
<p>
  ClickPesa requires a JWT bearer token generated via <code>POST /generate-token</code> using 
  the <code>client-id</code> and <code>api-key</code> request headers. The token is valid for exactly <strong>1 hour</strong>.
</p>
<p>
  The SDK encapsulates this logic inside <code>ClickPesa\Auth\TokenManager</code>. On every authenticated request:
</p>
<ol>
  <li>The SDK checks if a valid token exists in cache (in-memory for standalone PHP, or Laravel Cache in Laravel).</li>
  <li>If expired or missing, it automatically calls ClickPesa's <code>/generate-token</code> API and stores the token with a 5-minute safety buffer.</li>
  <li>If an unexpected 401 response occurs, it proactively flushes the token and transparently retries the call once.</li>
</ol>

<h2>Canonicalization & Checksum Engine</h2>
<p>
  When checksum is enabled, ClickPesa enforces an HMAC-SHA256 signature calculated on a canonicalized JSON payload:
</p>
<ol>
  <li><strong>Alphabetical Key Sorting</strong>: Recursively sorts all associative array/object keys alphabetically at every level.</li>
  <li><strong>Preserving Sequential Arrays</strong>: Numeric lists maintain their original order while sorting nested objects.</li>
  <li><strong>Excluding Checksum Fields</strong>: Automatically strips <code>checksum</code> and <code>checksumMethod</code> fields before calculation.</li>
  <li><strong>Compact JSON Serialization</strong>: Serialized using <code>json_encode($data, JSON_UNESCAPED_SLASHES)</code>.</li>
  <li><strong>HMAC-SHA256</strong>: Signed using your secret checksum key.</li>
</ol>

<pre><code>use ClickPesa\Security\Checksum;

// Generate outgoing checksum
$hash = Checksum::generate($secretKey, $payload);

// Timing-safe verification for incoming webhooks
$isValid = Checksum::verify($secretKey, $payload, $receivedChecksum);</code></pre>

<!-- SECTION 4: COLLECTIONS -->
<div class="page-break"></div>

<h1>4. Payment Collections API</h1>

<h2>1. Mobile USSD-PUSH</h2>
<p>Collect payments instantly by pushing USSD authorization prompts to customer handsets on Vodacom M-Pesa, Tigo-Pesa, Airtel Money, or Halopesa.</p>

<h3>Preview Fees & Available Networks</h3>
<pre><code>use ClickPesa\Laravel\Facades\ClickPesa;

$preview = ClickPesa::ussdPush()->preview([
    'amount'         => '10000',
    'currency'       => 'TZS',
    'orderReference' => 'ORD-1001',
    'phoneNumber'    => '255712345678',
    'fetchSenderDetails' => true,
]);</code></pre>

<h3>Initiate USSD-PUSH Prompt</h3>
<pre><code>$response = ClickPesa::ussdPush()->initiate([
    'amount'         => '10000',
    'currency'       => 'TZS',
    'orderReference' => 'ORD-1001',
    'phoneNumber'    => '255712345678',
]);

// Returns:
// [
//   'id' => 'PUSH12345',
//   'status' => 'PROCESSING',
//   'channel' => 'TIGO-PESA',
//   'orderReference' => 'ORD-1001',
//   'collectedAmount' => '10000'
// ]</code></pre>

<h2>2. Card Payments</h2>
<p>Accept payments from Visa, MasterCard, UnionPay, and American Express.</p>
<pre><code>$card = ClickPesa::cardPayments()->initiate([
    'amount'         => '50',
    'currency'       => 'USD',
    'orderReference' => 'CARD-ORD-902',
    'customer' => [
        'fullName'    => 'Jane Doe',
        'email'       => 'jane@example.com',
        'phoneNumber' => '255712345678',
    ],
]);

$redirectLink = $card['cardPaymentLink'];</code></pre>

<h2>3. Hosted Checkout Links</h2>
<p>Generate secure, hosted checkout pages supporting itemized orders or fixed totals.</p>
<pre><code>$link = ClickPesa::checkoutLinks()->generate([
    'totalPrice'     => '45000',
    'orderReference' => 'INV-2045',
    'orderCurrency'  => 'TZS',
    'customerName'   => 'Mathayo John',
    'customerEmail'  => 'mathayo@example.com',
    'customerPhone'  => '255712345678',
    'description'    => 'Payment for Software License',
]);

$hostedUrl = $link['checkoutLink'];</code></pre>

<!-- SECTION 5: BILLPAY & CRDB -->
<div class="page-break"></div>

<h1>5. BillPay Control Numbers & Direct Debit</h1>

<h2>BillPay Control Numbers</h2>
<p>
  Generate standard 14-digit Tanzanian control numbers for offline and online customer payments via banks and mobile money wallets.
</p>

<h3>Create Order Control Number (One-Time Invoice)</h3>
<pre><code>$orderBill = ClickPesa::billPay()->createOrder([
    'billDescription' => 'Water Bill - September 2026',
    'billAmount'      => 90000,
    'billPaymentMode' => 'ALLOW_PARTIAL_AND_OVER_PAYMENT',
    'billReference'   => 'INV-SEPT-01',
]);
$controlNumber = $orderBill['billPayNumber'];</code></pre>

<h3>Create Customer Control Number (Reusable Account)</h3>
<pre><code>$custBill = ClickPesa::billPay()->createCustomer();
$customerControlNo = $custBill['billPayNumber'];</code></pre>

<h3>Bulk Control Numbers (Up to 50 Items)</h3>
<pre><code>$bulk = ClickPesa::billPay()->bulkCreateOrders([
    ['billDescription' => 'Invoice 101', 'billAmount' => 15000],
    ['billDescription' => 'Invoice 102', 'billAmount' => 25000],
]);</code></pre>

<h2>CRDB Direct Debit Mandates</h2>
<p>Request recurring direct debit authorizations from customers' CRDB Bank accounts.</p>
<pre><code>// Request Mandate Approval
$mandate = ClickPesa::crdbDirectDebit()->requestMandate([
    'accountNumber' => '0150123456700',
    'amount'        => 50000,
    'orderReference'=> 'MANDATE-01',
    'startDate'     => '2026-10-01',
    'frequency'     => 'MONTHLY',
]);

// Inspect Mandate Details
$details = ClickPesa::crdbDirectDebit()->getMandate('MANDATE_REQ_ID_123');

// Cancel Active Mandate
ClickPesa::crdbDirectDebit()->cancelMandate(['mandateRequestId' => 'MANDATE_REQ_ID_123']);</code></pre>

<h2>Querying Payments</h2>
<pre><code>// Query specific payment attempts by Order Reference
$attempts = ClickPesa::payments()->get('ORD-1001');

// Query with pagination and filtering
$allPayments = ClickPesa::payments()->all([
    'status'    => 'SUCCESS',
    'startDate' => '2026-09-01',
    'limit'     => 25,
]);</code></pre>

<!-- SECTION 6: DISBURSEMENTS -->
<div class="page-break"></div>

<h1>6. Disbursements & Payouts API (All Types)</h1>

<div class="alert alert-warning">
  <strong>60-Second Cooldown:</strong> ClickPesa enforces a strict 60-second cooldown between payout creation requests per merchant. The SDK automatically catches this and raises a <code>RateLimitException</code> containing <code>$e->getRetryAfterSeconds()</code>.
</div>

<h2>1. Mobile Money (MNO) Payouts</h2>
<p>Disburse funds directly to Vodacom M-Pesa, Airtel Money, Mixx by Yas (Tigo), or HaloPesa wallets.</p>

<pre><code>// 1. Preview Fee & Available Balance
$preview = ClickPesa::mobileMoneyPayouts()->preview([
    'amount'         => 25000,
    'currency'       => 'TZS',
    'orderReference' => 'PAY-MNO-01',
    'phoneNumber'    => '255712345678',
]);
$fee = $preview['fee']; // e.g., 700 TZS

// 2. Execute Payout
$payout = ClickPesa::mobileMoneyPayouts()->create([
    'amount'         => 25000,
    'currency'       => 'TZS',
    'orderReference' => 'PAY-MNO-01',
    'phoneNumber'    => '255712345678',
]);</code></pre>

<h2>2. Bank Transfers (EFT & TISS)</h2>
<p>Disburse payouts to any commercial bank account in Tanzania using Account Number and BIC. Transfers up to 20M TZS use EFT (fee: 2,360 TZS); transfers above 20M TZS must use TISS (fee: 11,800 TZS).</p>
<pre><code>// 1. Preview Bank Payout
$preview = ClickPesa::bankPayouts()->preview([
    'amount'        => 500000,
    'currency'      => 'TZS',
    'orderReference'=> 'PAY-BNK-01',
    'accountNumber' => '0150123456700',
    'bic'           => 'CORUTZTZ', // CRDB Bank BIC
]);

// 2. Execute Bank Payout
$bankPayout = ClickPesa::bankPayouts()->create([
    'amount'        => 500000,
    'currency'      => 'TZS',
    'orderReference'=> 'PAY-BNK-01',
    'accountNumber' => '0150123456700',
    'accountName'   => 'Acme Trading Ltd',
    'bic'           => 'CORUTZTZ',
    'transferType'  => 'ACH', // 'ACH' for EFT, 'RTGS' for TISS
]);</code></pre>

<h2>3. Merchant Lipa Namba Payouts</h2>
<p>Send money directly to merchant Lipa Namba numbers via mobile networks.</p>
<pre><code>// 1. Get providers (e.g. Vodacom 503, Airtel 502)
$providers = ClickPesa::lipaNambaPayouts()->providers();

// 2. Send to Lipa Namba
$lnPayout = ClickPesa::lipaNambaPayouts()->create([
    'amount'         => 15000,
    'currency'       => 'TZS',
    'orderReference' => 'PAY-LN-01',
    'lipaNamba'      => '48001268',
    'providerCode'   => '503',
]);</code></pre>

<h2>4. TIPS TanQR Payouts (Direct to QR Code)</h2>
<pre><code>$qrPayout = ClickPesa::lipaNambaPayouts()->create([
    'amount'         => 30000,
    'currency'       => 'TZS',
    'orderReference' => 'PAY-QR-01',
    'qrCode'         => '00020101021226...',
]);</code></pre>

<h2>5. Hosted Payout Links</h2>
<pre><code>// Generate a link where payee selects mobile money or bank
$link = ClickPesa::payoutLinks()->create([
    'amount'         => 100000,
    'currency'       => 'TZS',
    'orderReference' => 'PAY-LINK-01',
    'recipientName'  => 'Juma Hamisi',
]);
$claimUrl = $link['payoutUrl'];</code></pre>

<h2>6. Querying Payout Status & Pagination</h2>
<pre><code>// Query specific payout by Order Reference
$payoutStatus = ClickPesa::payouts()->get('PAY-MNO-01');

// Filter payout history with pagination
$history = ClickPesa::payouts()->all([
    'status'    => 'SUCCESS',
    'startDate' => '2026-09-01',
    'limit'     => 50,
]);</code></pre>

<!-- SECTION 7: PRICING & FEE CALCULATOR -->
<div class="page-break"></div>

<h1>7. Pricing, Fee Bearers & Offline Fee Calculator</h1>

<p>
  ClickPesa differentiates between channels where fees are charged to the <strong>customer</strong> versus channels where fees are deducted from the <strong>merchant</strong>.
</p>

<h2>ClickPesa Tariff & Fee Bearers Matrix</h2>
<table>
  <thead>
    <tr>
      <th>Payment Channel</th>
      <th>Fee Structure</th>
      <th>Fee Bearer</th>
      <th>Transaction Limits</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>USSD Push</strong></td>
      <td>Tiered Slab (54 – 7,960 TZS)</td>
      <td><strong>Customer</strong></td>
      <td>500 to 3,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>Card Payments</strong></td>
      <td>4.85%</td>
      <td><strong>Customer</strong></td>
      <td>Visa, Mastercard, UnionPay</td>
    </tr>
    <tr>
      <td><strong>BillPay (M-Pesa & Airtel)</strong></td>
      <td>1.0%</td>
      <td><strong>Merchant</strong></td>
      <td>500 to 5,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>BillPay (HaloPesa)</strong></td>
      <td>2.0%</td>
      <td><strong>Merchant</strong></td>
      <td>500 to 5,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>BillPay (Mixx by Yas / Tigo)</strong></td>
      <td>2.5%</td>
      <td><strong>Merchant</strong></td>
      <td>500 to 5,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>CRDB BillPay</strong></td>
      <td>1.0%</td>
      <td><strong>Merchant</strong></td>
      <td>1,000 to 100,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>CRDB Direct Debit</strong></td>
      <td>2,000 TZS Flat</td>
      <td><strong>Merchant</strong></td>
      <td>Up to 100,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>TIPS TanQR Collection</strong></td>
      <td>2.0%</td>
      <td><strong>Merchant</strong></td>
      <td>TIPS QR Merchant Code</td>
    </tr>
    <tr>
      <td><strong>Mobile Money / TanQR Payouts</strong></td>
      <td>Tiered Slab (52 – 9,890 TZS)</td>
      <td><strong>Configurable</strong></td>
      <td>100 to 5,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>Bank EFT Payout</strong></td>
      <td>2,360 TZS Flat</td>
      <td><strong>Configurable</strong></td>
      <td>Up to 20,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>Bank TISS Payout (TZS)</strong></td>
      <td>11,800 TZS Flat</td>
      <td><strong>Configurable</strong></td>
      <td>Up to 1,000,000,000 TZS</td>
    </tr>
    <tr>
      <td><strong>Bank TISS Payout (USD)</strong></td>
      <td>$7.50 USD Flat</td>
      <td><strong>Configurable</strong></td>
      <td>Up to $1,000,000 USD</td>
    </tr>
  </tbody>
</table>

<h2>Instant Offline Fee Estimation (<code>ClickPesa::fees()</code>)</h2>
<pre><code>// 1. Calculate USSD Push fee
$ussdFee = ClickPesa::fees()->calculateUssdPushFee(15000); // 920.0 TZS

// 2. Calculate Card Payment fee
$cardFee = ClickPesa::fees()->calculateCardFee(50000); // 2,425.0 TZS (4.85%)

// 3. Calculate BillPay fee
$mpesaFee = ClickPesa::fees()->calculateBillPayFee('mpesa', 100000); // 1,000.0 TZS (1%)

// 4. Calculate Net Merchant Settlement
$net = ClickPesa::fees()->calculateNetSettlement('billpay_mpesa', 100000); // 99,000.0 TZS

// 5. Payout Budgeting (Absorb Fee vs Deduct from Payee)
$absorbed = ClickPesa::fees()->calculatePayoutDeduction('mobile_money', 50000, absorbFee: true);
// Total merchant debited: 51,460 TZS; Payee receives: 50,000 TZS

$deducted = ClickPesa::fees()->calculatePayoutDeduction('mobile_money', 50000, absorbFee: false);
// Total merchant debited: 50,000 TZS; Payee receives: 48,540 TZS

// 6. Pre-Flight Limits Validation
ChannelLimits::validate('ussd_push', 15000); // Passes without network call!</code></pre>

<h2>Environment Variables for Tariff Configuration</h2>
<p>All fees are fully customizable in <code>.env</code>:</p>
<pre><code># .env Pricing Overrides
CLICKPESA_FEE_CARD_PERCENT=4.85
CLICKPESA_FEE_BILLPAY_MPESA_PERCENT=1.0
CLICKPESA_FEE_BILLPAY_HALOPESA_PERCENT=2.0
CLICKPESA_FEE_BILLPAY_MIXX_PERCENT=2.5
CLICKPESA_FEE_BILLPAY_CRDB_PERCENT=1.0
CLICKPESA_FEE_CRDB_DIRECT_DEBIT=2000
CLICKPESA_FEE_BANK_EFT=2360
CLICKPESA_FEE_BANK_TISS_TZS=11800
CLICKPESA_FEE_BANK_TISS_USD=7.50
CLICKPESA_BANK_EFT_MAX_THRESHOLD=20000000</code></pre>

<!-- SECTION 8: WEBHOOKS -->
<div class="page-break"></div>

<h1>8. Webhooks & Event Processing</h1>

<p>
  ClickPesa sends asynchronous HTTP POST notifications when transactions complete, fail, or reverse.
</p>

<h2>Setting Up Webhook Route in Laravel</h2>
<p>Register the route macro in <code>routes/api.php</code>:</p>
<pre><code>use Illuminate\Support\Facades\Route;

// Registers POST /clickpesa/webhooks
Route::clickpesaWebhooks('clickpesa/webhooks');</code></pre>

<h2>Listening to Dispatched Laravel Events</h2>
<table>
  <thead>
    <tr>
      <th>ClickPesa Event Name</th>
      <th>Dispatched Laravel Event</th>
      <th>Key Attributes</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>PAYMENT RECEIVED</code></td>
      <td><code>ClickPesa\Laravel\Events\PaymentReceived</code></td>
      <td><code>getOrderReference()</code>, <code>getCollectedAmount()</code>, <code>getCustomer()</code></td>
    </tr>
    <tr>
      <td><code>PAYMENT FAILED</code></td>
      <td><code>ClickPesa\Laravel\Events\PaymentFailed</code></td>
      <td><code>getOrderReference()</code>, <code>getMessage()</code></td>
    </tr>
    <tr>
      <td><code>PAYOUT INITIATED</code></td>
      <td><code>ClickPesa\Laravel\Events\PayoutInitiated</code></td>
      <td><code>getOrderReference()</code>, <code>getAmount()</code>, <code>getFee()</code></td>
    </tr>
    <tr>
      <td><code>PAYOUT REFUNDED</code></td>
      <td><code>ClickPesa\Laravel\Events\PayoutRefunded</code></td>
      <td><code>getOrderReference()</code>, <code>getRefundMessage()</code></td>
    </tr>
    <tr>
      <td><code>PAYOUT REVERSED</code></td>
      <td><code>ClickPesa\Laravel\Events\PayoutReversed</code></td>
      <td><code>getOrderReference()</code>, <code>getReverseMessage()</code></td>
    </tr>
    <tr>
      <td><code>DEPOSIT RECEIVED</code></td>
      <td><code>ClickPesa\Laravel\Events\DepositReceived</code></td>
      <td><code>getDepositAmount()</code>, <code>getPaymentReference()</code></td>
    </tr>
  </tbody>
</table>

<h3>Example Listener Implementation</h3>
<pre><code>namespace App\Listeners;

use ClickPesa\Laravel\Events\PaymentReceived;

class MarkOrderAsPaid
{{
    public function handle(PaymentReceived $event): void
    {{
        $orderReference = $event->getOrderReference();
        $amount = $event->getCollectedAmount();
        $paymentRef = $event->getPaymentReference();

        // Update database order status
        $order = Order::where('reference', $orderReference)->first();
        if ($order) {{
            $order->update([
                'status' => 'paid',
                'clickpesa_payment_reference' => $paymentRef,
            ]);
        }}
    }}
}}</code></pre>

<!-- SECTION 9: STANDALONE PHP & TESTING -->
<div class="page-break"></div>

<h1>9. Standalone PHP & Application Testing</h1>

<h2>Using the SDK in Vanilla PHP / Non-Laravel Projects</h2>
<pre><code>require_once __DIR__ . '/vendor/autoload.php';

use ClickPesa\ClickPesaClient;

$client = new ClickPesaClient([
    'client_id'        => 'YOUR_CLIENT_ID',
    'api_key'          => 'YOUR_API_KEY',
    'checksum_key'     => 'YOUR_CHECKSUM_KEY',
    'checksum_enabled' => true,
]);

// Initiate USSD-PUSH
$res = $client->ussdPush()->initiate([
    'amount'         => '10000',
    'currency'       => 'TZS',
    'orderReference' => 'PHP_ORD_1',
    'phoneNumber'    => '255712345678',
]);</code></pre>

<h2>Testing & Mocking with <code>ClickPesa::fake()</code></h2>
<p>
  The SDK includes a first-class fake mechanism so your tests never make live network calls:
</p>

<pre><code>use ClickPesa\Laravel\Facades\ClickPesa;
use Tests\TestCase;

class CheckoutTest extends TestCase
{{
    public function test_user_can_checkout(): void
    {{
        // Mock all ClickPesa API calls
        ClickPesa::fake();

        $response = $this->postJson('/checkout', [
            'amount' => 5000,
            'phone' => '255712345678',
        ]);

        $response->assertOk();

        // Assert payment call occurred with expected parameters
        ClickPesa::assertInitiatedUssdPush(function ($data) {{
            return $data['amount'] === '5000' && $data['phoneNumber'] === '255712345678';
        }});

        // Assert no unauthorized payouts were triggered
        ClickPesa::assertNotSent('/payouts/create-bank-payout');
    }}
}}</code></pre>

<!-- SECTION 10: AUTHORS & SUPPORT -->
<div class="page-break"></div>

<h1>10. Authors, Support & Commercial Inquiries</h1>

<div style="text-align: center; margin: 40px 0;">
  <img src="data:image/png;base64,{logo_base64}" alt="UBITECH SOLUTIONS LIMITED" style="max-width: 320px;">
</div>

<div class="cover-author-box" style="max-width: 550px;">
  <h2 style="margin: 0 0 12px 0; border: none; color: #0284c7;">UBITECH SOLUTIONS LIMITED</h2>
  <p style="font-size: 14px; margin-bottom: 16px;">
    We build custom payment gateways, enterprise fintech solutions, ERP integrations, 
    and scalable mobile/web platforms across East Africa.
  </p>

  <p><strong>📞 Telephone / WhatsApp:</strong> <span class="phone">+255 766 192 332</span></p>
  <p><strong>🌐 GitHub Organization:</strong> <a href="https://github.com/ubitechsolutionsltd">github.com/ubitechsolutionsltd</a></p>
  <p><strong>📦 Package Repository:</strong> <a href="https://github.com/ubitechsolutionsltd/clickpesa-laravel-package">clickpesa-laravel-package</a></p>
  <p style="margin-top: 16px; font-size: 12px; color: #94a3b8;">
    Licensed under the MIT License. &copy; 2026 UBITECH SOLUTIONS LIMITED. All rights reserved.
  </p>
</div>

</body>
</html>
"""

    with open(output_html, "w", encoding="utf-8") as f:
        f.write(html_content)

    print(f"Generated HTML documentation at: {output_html}")

    # Use Microsoft Edge to render HTML to PDF
    msedge_exe = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
    if not os.path.exists(msedge_exe):
        msedge_exe = r"C:\Program Files\Microsoft\Edge\Application\msedge.exe"

    cmd = [
        msedge_exe,
        "--headless",
        "--disable-gpu",
        "--no-pdf-header-footer",
        f"--print-to-pdf={output_pdf}",
        f"file:///{output_html.replace(os.sep, '/')}"
    ]

    print("Rendering PDF with Edge...")
    result = subprocess.run(cmd, capture_output=True, text=True)
    if os.path.exists(output_pdf) and os.path.getsize(output_pdf) > 0:
        print(f"SUCCESS: {output_pdf} generated! Size: {os.path.getsize(output_pdf)} bytes.")
    else:
        print(f"Error rendering PDF: {result.stderr}")

if __name__ == "__main__":
    create_documentation_pdf()
