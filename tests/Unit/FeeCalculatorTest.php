<?php

declare(strict_types=1);

namespace ClickPesa\Tests\Unit;

use ClickPesa\ClickPesaClient;
use ClickPesa\Config;
use ClickPesa\Exceptions\ValidationException;
use ClickPesa\Pricing\ChannelLimits;
use ClickPesa\Pricing\FeeBearer;
use ClickPesa\Pricing\FeeCalculator;
use PHPUnit\Framework\TestCase;

class FeeCalculatorTest extends TestCase
{
    private FeeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new FeeCalculator();
    }

    public function test_ussd_push_fee_slabs(): void
    {
        // 500 - 899 -> 54
        $this->assertEquals(54.0, $this->calculator->calculateUssdPushFee(500));
        $this->assertEquals(54.0, $this->calculator->calculateUssdPushFee(750));

        // 9000 - 19999 -> 920
        $this->assertEquals(920.0, $this->calculator->calculateUssdPushFee(15000));

        // 50000 - 95999 -> 2136
        $this->assertEquals(2136.0, $this->calculator->calculateUssdPushFee(50000));

        // 2,000,000 - 3,000,000 -> 7960
        $this->assertEquals(7960.0, $this->calculator->calculateUssdPushFee(2500000));
    }

    public function test_card_fee_calculation(): void
    {
        // Default 4.85%
        $this->assertEquals(2425.0, $this->calculator->calculateCardFee(50000));
        $this->assertEquals(485.0, $this->calculator->calculateCardFee(10000));
    }

    public function test_billpay_fees_across_providers(): void
    {
        $amount = 100000.0;

        // M-Pesa & Airtel: 1.0% -> 1,000 TZS
        $this->assertEquals(1000.0, $this->calculator->calculateBillPayFee('mpesa', $amount));
        $this->assertEquals(1000.0, $this->calculator->calculateBillPayFee('airtel', $amount));

        // HaloPesa: 2.0% -> 2,000 TZS
        $this->assertEquals(2000.0, $this->calculator->calculateBillPayFee('halopesa', $amount));

        // Mixx by Yas (Tigo): 2.5% -> 2,500 TZS
        $this->assertEquals(2500.0, $this->calculator->calculateBillPayFee('mixx', $amount));
        $this->assertEquals(2500.0, $this->calculator->calculateBillPayFee('tigo', $amount));

        // CRDB: 1.0% -> 1,000 TZS
        $this->assertEquals(1000.0, $this->calculator->calculateBillPayFee('crdb', $amount));
    }

    public function test_crdb_direct_debit_fee(): void
    {
        $this->assertEquals(2000.0, $this->calculator->calculateCrdbDirectDebitFee());
    }

    public function test_tanqr_collection_fee(): void
    {
        // 2.0%
        $this->assertEquals(200.0, $this->calculator->calculateTanQrCollectionFee(10000));
        $this->assertEquals(2000.0, $this->calculator->calculateTanQrCollectionFee(100000));
    }

    public function test_payout_fee_slabs(): void
    {
        // 100 - 999 -> 52
        $this->assertEquals(52.0, $this->calculator->calculatePayoutFee(500));

        // 10000 - 14999 -> 642
        $this->assertEquals(642.0, $this->calculator->calculatePayoutFee(12000));

        // 100000 - 199999 -> 1868
        $this->assertEquals(1868.0, $this->calculator->calculatePayoutFee(150000));

        // 3000001 - 5000000 -> 9890
        $this->assertEquals(9890.0, $this->calculator->calculatePayoutFee(4500000));
    }

    public function test_bank_payout_fee_and_auto_routing(): void
    {
        // <= 20M TZS defaults to EFT (2,360 TZS)
        $this->assertEquals(2360.0, $this->calculator->calculateBankPayoutFee(5000000, 'auto', 'TZS'));
        $this->assertEquals(2360.0, $this->calculator->calculateBankPayoutFee(20000000, 'EFT', 'TZS'));

        // > 20M TZS auto-routes to TISS (11,800 TZS)
        $this->assertEquals(11800.0, $this->calculator->calculateBankPayoutFee(25000000, 'auto', 'TZS'));
        $this->assertEquals(11800.0, $this->calculator->calculateBankPayoutFee(10000000, 'TISS', 'TZS'));

        // USD transfer routes to TISS USD ($7.50)
        $this->assertEquals(7.50, $this->calculator->calculateBankPayoutFee(5000, 'auto', 'USD'));
    }

    public function test_bank_transfer_recommendation(): void
    {
        $this->assertEquals('EFT', ChannelLimits::recommendBankTransferMethod(5000000, 'TZS'));
        $this->assertEquals('EFT', ChannelLimits::recommendBankTransferMethod(20000000, 'TZS'));
        $this->assertEquals('TISS', ChannelLimits::recommendBankTransferMethod(20000001, 'TZS'));
        $this->assertEquals('TISS', ChannelLimits::recommendBankTransferMethod(1000, 'USD'));
    }

    public function test_channel_limits_validation(): void
    {
        // Valid amount
        ChannelLimits::validate('ussd_push', 15000);
        $this->assertTrue(true);

        // Below min USSD (500)
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('below minimum allowed limit');
        ChannelLimits::validate('ussd_push', 300);
    }

    public function test_channel_limits_validation_above_max(): void
    {
        // Above max USSD (3,000,000)
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('exceeds maximum allowed limit');
        ChannelLimits::validate('ussd_push', 3500000);
    }

    public function test_net_settlement_calculation(): void
    {
        // 100,000 gross with M-Pesa BillPay (1%) -> 99,000 net
        $this->assertEquals(99000.0, $this->calculator->calculateNetSettlement('billpay_mpesa', 100000));

        // 100,000 gross with TanQR (2%) -> 98,000 net
        $this->assertEquals(98000.0, $this->calculator->calculateNetSettlement('tanqr_collection', 100000));
    }

    public function test_payout_deduction_budgeting(): void
    {
        // Absorb fee: merchant pays 50,000 + 1,460 = 51,460; recipient gets 50,000
        $absorbed = $this->calculator->calculatePayoutDeduction('mobile_money', 50000, absorbFee: true);
        $this->assertEquals(51460.0, $absorbed['total_debited_from_merchant']);
        $this->assertEquals(50000.0, $absorbed['net_received_by_recipient']);
        $this->assertEquals(1460.0, $absorbed['fee']);

        // Deduct fee: merchant pays 50,000; recipient gets 50,000 - 1,460 = 48,540
        $deducted = $this->calculator->calculatePayoutDeduction('mobile_money', 50000, absorbFee: false);
        $this->assertEquals(50000.0, $deducted['total_debited_from_merchant']);
        $this->assertEquals(48540.0, $deducted['net_received_by_recipient']);
        $this->assertEquals(1460.0, $deducted['fee']);
    }

    public function test_get_breakdown(): void
    {
        $breakdown = $this->calculator->getBreakdown('ussd_push', 15000);
        $this->assertEquals(15000.0, $breakdown['amount']);
        $this->assertEquals(920.0, $breakdown['fee']);
        $this->assertEquals(15920.0, $breakdown['total_charged_to_customer']);
        $this->assertEquals(FeeBearer::CUSTOMER, $breakdown['fee_bearer']);

        $billPayBreakdown = $this->calculator->getBreakdown('billpay_mpesa', 100000);
        $this->assertEquals(1000.0, $billPayBreakdown['fee']);
        $this->assertEquals(99000.0, $billPayBreakdown['net_merchant_settlement']);
        $this->assertEquals(FeeBearer::MERCHANT, $billPayBreakdown['fee_bearer']);
    }

    public function test_custom_pricing_overrides(): void
    {
        $customConfig = new Config([
            'client_id' => 'test_id',
            'api_key' => 'test_key',
            'pricing' => [
                'card_percentage' => 3.5, // negotiated custom card rate
                'billpay_mpesa_percentage' => 0.8,
                'bank_eft_fee' => 1500.0,
            ],
        ]);

        $customCalc = new FeeCalculator($customConfig);

        $this->assertEquals(350.0, $customCalc->calculateCardFee(10000));
        $this->assertEquals(800.0, $customCalc->calculateBillPayFee('mpesa', 100000));
        $this->assertEquals(1500.0, $customCalc->calculateBankPayoutFee(500000, 'EFT'));
    }

    public function test_client_fees_accessor(): void
    {
        $client = new ClickPesaClient([
            'client_id' => 'test_id',
            'api_key' => 'test_key',
        ]);

        $this->assertInstanceOf(FeeCalculator::class, $client->fees());
        $this->assertEquals(920.0, $client->fees()->calculateUssdPushFee(15000));
    }
}
