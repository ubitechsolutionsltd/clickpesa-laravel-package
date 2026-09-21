<?php

declare(strict_types=1);

namespace ClickPesa;

use ClickPesa\Auth\InMemoryTokenCache;
use ClickPesa\Auth\TokenCacheInterface;
use ClickPesa\Auth\TokenManager;
use ClickPesa\Http\GuzzleHttpClient;
use ClickPesa\Http\HttpClientInterface;
use ClickPesa\Resources\Account\BalanceResource;
use ClickPesa\Resources\Account\BankListResource;
use ClickPesa\Resources\Account\ExchangeRateResource;
use ClickPesa\Resources\Collection\BillPayResource;
use ClickPesa\Resources\Collection\CardPaymentResource;
use ClickPesa\Resources\Collection\CheckoutLinkResource;
use ClickPesa\Resources\Collection\CrdbDirectDebitResource;
use ClickPesa\Resources\Collection\PaymentStatusResource;
use ClickPesa\Resources\Collection\UssdPushResource;
use ClickPesa\Resources\Disbursement\BankPayoutResource;
use ClickPesa\Resources\Disbursement\LipaNambaPayoutResource;
use ClickPesa\Resources\Disbursement\MobileMoneyPayoutResource;
use ClickPesa\Resources\Disbursement\PayoutLinkResource;
use ClickPesa\Resources\Disbursement\PayoutStatusResource;

class ClickPesaClient
{
    private Config $config;
    private TokenManager $tokenManager;
    private HttpClientInterface $httpClient;

    // Cached resource instances
    private ?UssdPushResource $ussdPush = null;
    private ?CardPaymentResource $cardPayments = null;
    private ?CheckoutLinkResource $checkoutLinks = null;
    private ?BillPayResource $billPay = null;
    private ?CrdbDirectDebitResource $crdbDirectDebit = null;
    private ?PaymentStatusResource $payments = null;
    private ?MobileMoneyPayoutResource $mobileMoneyPayouts = null;
    private ?BankPayoutResource $bankPayouts = null;
    private ?LipaNambaPayoutResource $lipaNambaPayouts = null;
    private ?PayoutLinkResource $payoutLinks = null;
    private ?PayoutStatusResource $payouts = null;
    private ?BalanceResource $balance = null;
    private ?BankListResource $banks = null;
    private ?ExchangeRateResource $exchangeRates = null;

    /**
     * @param Config|array<string, mixed> $config
     * @param TokenCacheInterface|null $tokenCache
     * @param HttpClientInterface|null $httpClient
     */
    public function __construct(
        Config|array $config,
        ?TokenCacheInterface $tokenCache = null,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->config = $config instanceof Config ? $config : new Config($config);
        $this->tokenManager = new TokenManager($this->config, $tokenCache ?? new InMemoryTokenCache());
        $this->httpClient = $httpClient ?? new GuzzleHttpClient($this->config, $this->tokenManager);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getTokenManager(): TokenManager
    {
        return $this->tokenManager;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    // Collections (Payments)

    public function ussdPush(): UssdPushResource
    {
        return $this->ussdPush ??= new UssdPushResource($this->httpClient);
    }

    public function cardPayments(): CardPaymentResource
    {
        return $this->cardPayments ??= new CardPaymentResource($this->httpClient);
    }

    public function checkoutLinks(): CheckoutLinkResource
    {
        return $this->checkoutLinks ??= new CheckoutLinkResource($this->httpClient);
    }

    public function billPay(): BillPayResource
    {
        return $this->billPay ??= new BillPayResource($this->httpClient);
    }

    public function crdbDirectDebit(): CrdbDirectDebitResource
    {
        return $this->crdbDirectDebit ??= new CrdbDirectDebitResource($this->httpClient);
    }

    public function payments(): PaymentStatusResource
    {
        return $this->payments ??= new PaymentStatusResource($this->httpClient);
    }

    // Disbursements (Payouts)

    public function mobileMoneyPayouts(): MobileMoneyPayoutResource
    {
        return $this->mobileMoneyPayouts ??= new MobileMoneyPayoutResource($this->httpClient);
    }

    public function bankPayouts(): BankPayoutResource
    {
        return $this->bankPayouts ??= new BankPayoutResource($this->httpClient);
    }

    public function lipaNambaPayouts(): LipaNambaPayoutResource
    {
        return $this->lipaNambaPayouts ??= new LipaNambaPayoutResource($this->httpClient);
    }

    public function payoutLinks(): PayoutLinkResource
    {
        return $this->payoutLinks ??= new PayoutLinkResource($this->httpClient);
    }

    public function payouts(): PayoutStatusResource
    {
        return $this->payouts ??= new PayoutStatusResource($this->httpClient);
    }

    // Account & Utilities

    public function balance(): BalanceResource
    {
        return $this->balance ??= new BalanceResource($this->httpClient);
    }

    public function banks(): BankListResource
    {
        return $this->banks ??= new BankListResource($this->httpClient);
    }

    public function exchangeRates(): ExchangeRateResource
    {
        return $this->exchangeRates ??= new ExchangeRateResource($this->httpClient);
    }
}
