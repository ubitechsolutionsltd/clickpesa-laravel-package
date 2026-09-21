<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Facades;

use ClickPesa\ClickPesaClient;
use ClickPesa\Laravel\Testing\ClickPesaFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \ClickPesa\Resources\Collection\UssdPushResource ussdPush()
 * @method static \ClickPesa\Resources\Collection\CardPaymentResource cardPayments()
 * @method static \ClickPesa\Resources\Collection\CheckoutLinkResource checkoutLinks()
 * @method static \ClickPesa\Resources\Collection\BillPayResource billPay()
 * @method static \ClickPesa\Resources\Collection\CrdbDirectDebitResource crdbDirectDebit()
 * @method static \ClickPesa\Resources\Collection\PaymentStatusResource payments()
 * @method static \ClickPesa\Resources\Disbursement\MobileMoneyPayoutResource mobileMoneyPayouts()
 * @method static \ClickPesa\Resources\Disbursement\BankPayoutResource bankPayouts()
 * @method static \ClickPesa\Resources\Disbursement\LipaNambaPayoutResource lipaNambaPayouts()
 * @method static \ClickPesa\Resources\Disbursement\PayoutLinkResource payoutLinks()
 * @method static \ClickPesa\Resources\Disbursement\PayoutStatusResource payouts()
 * @method static \ClickPesa\Resources\Account\BalanceResource balance()
 * @method static \ClickPesa\Resources\Account\BankListResource banks()
 * @method static \ClickPesa\Resources\Account\ExchangeRateResource exchangeRates()
 * @method static \ClickPesa\Config getConfig()
 * @method static \ClickPesa\Auth\TokenManager getTokenManager()
 * @method static \ClickPesa\Http\HttpClientInterface getHttpClient()
 *
 * @see \ClickPesa\ClickPesaClient
 */
class ClickPesa extends Facade
{
    /**
     * Replace the bound instance with a fake for testing.
     */
    public static function fake(array $responses = []): ClickPesaFake
    {
        $fake = new ClickPesaFake($responses);
        static::swap($fake);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return 'clickpesa';
    }
}
