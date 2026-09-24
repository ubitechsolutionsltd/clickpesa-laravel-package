<?php

declare(strict_types=1);

namespace ClickPesa\Pricing;

use ClickPesa\Config;
use InvalidArgumentException;

class FeeCalculator
{
    public const DEFAULT_USSD_PUSH_SLABS = [
        ['min' => 500,     'max' => 899,       'fee' => 54],
        ['min' => 900,     'max' => 1999,      'fee' => 92],
        ['min' => 2000,    'max' => 2999,      'fee' => 124],
        ['min' => 3000,    'max' => 3999,      'fee' => 230],
        ['min' => 4000,    'max' => 4399,      'fee' => 380],
        ['min' => 4400,    'max' => 8999,      'fee' => 580],
        ['min' => 9000,    'max' => 19999,     'fee' => 920],
        ['min' => 20000,   'max' => 39999,     'fee' => 1150],
        ['min' => 40000,   'max' => 49999,     'fee' => 1572],
        ['min' => 50000,   'max' => 95999,     'fee' => 2136],
        ['min' => 96000,   'max' => 199999,    'fee' => 3240],
        ['min' => 200000,  'max' => 299999,    'fee' => 3660],
        ['min' => 300000,  'max' => 399999,    'fee' => 4080],
        ['min' => 400000,  'max' => 499999,    'fee' => 4340],
        ['min' => 500000,  'max' => 599999,    'fee' => 4820],
        ['min' => 600000,  'max' => 799999,    'fee' => 5230],
        ['min' => 800000,  'max' => 999999,    'fee' => 6146],
        ['min' => 1000000, 'max' => 1999999,   'fee' => 7210],
        ['min' => 2000000, 'max' => 3000000,   'fee' => 7960],
    ];

    public const DEFAULT_PAYOUT_SLABS = [
        ['min' => 100,     'max' => 999,       'fee' => 52],
        ['min' => 1000,    'max' => 1999,      'fee' => 72],
        ['min' => 2000,    'max' => 2999,      'fee' => 104],
        ['min' => 3000,    'max' => 3999,      'fee' => 116],
        ['min' => 4000,    'max' => 4999,      'fee' => 168],
        ['min' => 5000,    'max' => 6999,      'fee' => 234],
        ['min' => 7000,    'max' => 7999,      'fee' => 360],
        ['min' => 8000,    'max' => 9999,      'fee' => 430],
        ['min' => 10000,   'max' => 14999,     'fee' => 642],
        ['min' => 15000,   'max' => 19999,     'fee' => 680],
        ['min' => 20000,   'max' => 29999,     'fee' => 700],
        ['min' => 30000,   'max' => 39999,     'fee' => 980],
        ['min' => 40000,   'max' => 49999,     'fee' => 1038],
        ['min' => 50000,   'max' => 99999,     'fee' => 1460],
        ['min' => 100000,  'max' => 199999,    'fee' => 1868],
        ['min' => 200000,  'max' => 299999,    'fee' => 2220],
        ['min' => 300000,  'max' => 399999,    'fee' => 3180],
        ['min' => 400000,  'max' => 499999,    'fee' => 3764],
        ['min' => 500000,  'max' => 599999,    'fee' => 4672],
        ['min' => 600000,  'max' => 699999,    'fee' => 5712],
        ['min' => 700000,  'max' => 799999,    'fee' => 6560],
        ['min' => 800000,  'max' => 899999,    'fee' => 7800],
        ['min' => 900000,  'max' => 1000000,   'fee' => 8508],
        ['min' => 1000001, 'max' => 3000000,   'fee' => 9346],
        ['min' => 3000001, 'max' => 5000000,   'fee' => 9890],
    ];

    /** @var array<string, mixed> */
    private array $pricing;

    public function __construct(?Config $config = null)
    {
        $this->pricing = $config?->getPricing() ?? [];
    }

    /**
     * Calculate USSD Push fee based on the 19-slab tariff (charged to customer).
     */
    public function calculateUssdPushFee(float $amount): float
    {
        $slabs = $this->pricing['ussd_push_slabs'] ?? self::DEFAULT_USSD_PUSH_SLABS;

        foreach ($slabs as $slab) {
            if ($amount >= $slab['min'] && $amount <= $slab['max']) {
                return (float) $slab['fee'];
            }
        }

        if ($amount < ChannelLimits::USSD_PUSH_MIN) {
            return (float) ($slabs[0]['fee'] ?? 54.0);
        }

        // Above max slab: return highest slab fee
        return (float) ($slabs[array_key_last($slabs)]['fee'] ?? 7960.0);
    }

    /**
     * Calculate Card payment fee (charged to customer).
     * Default: 4.85% (configurable via config/clickpesa.php or .env).
     */
    public function calculateCardFee(float $amount): float
    {
        $rate = (float) ($this->pricing['card_percentage'] ?? 4.85);

        return round(($amount * ($rate / 100.0)), 2);
    }

    /**
     * Calculate BillPay collection fee (charged to merchant/organization).
     * M-Pesa / Airtel: 1.0%, HaloPesa: 2.0%, Mixx by Yas (Tigo): 2.5%, CRDB: 1.0%.
     */
    public function calculateBillPayFee(string $channel, float $amount): float
    {
        $normalized = strtolower(str_replace(['-', ' '], '_', $channel));

        $rate = match ($normalized) {
            'halopesa' => (float) ($this->pricing['billpay_halopesa_percentage'] ?? 2.0),
            'mixx', 'mixx_by_yas', 'tigo', 'tigopesa' => (float) ($this->pricing['billpay_mixx_percentage'] ?? 2.5),
            'crdb' => (float) ($this->pricing['billpay_crdb_percentage'] ?? 1.0),
            'airtel', 'airtel_money' => (float) ($this->pricing['billpay_airtel_percentage'] ?? 1.0),
            default => (float) ($this->pricing['billpay_mpesa_percentage'] ?? 1.0), // mpesa or default
        };

        return round(($amount * ($rate / 100.0)), 2);
    }

    /**
     * Calculate CRDB Direct Debit fee per transaction (charged to merchant/organization).
     * Default: 2,000 TZS flat.
     */
    public function calculateCrdbDirectDebitFee(): float
    {
        return (float) ($this->pricing['crdb_direct_debit_fee'] ?? 2000.0);
    }

    /**
     * Calculate TIPS TanQR / Lipa Namba collection fee (charged to merchant).
     * Default: 2.0%.
     */
    public function calculateTanQrCollectionFee(float $amount): float
    {
        $rate = (float) ($this->pricing['tanqr_collection_percentage'] ?? 2.0);

        return round(($amount * ($rate / 100.0)), 2);
    }

    /**
     * Calculate Mobile Money or TanQR payout fee based on ClickPesa's 25-slab tier.
     */
    public function calculatePayoutFee(float $amount): float
    {
        $slabs = $this->pricing['payout_slabs'] ?? self::DEFAULT_PAYOUT_SLABS;

        foreach ($slabs as $slab) {
            if ($amount >= $slab['min'] && $amount <= $slab['max']) {
                return (float) $slab['fee'];
            }
        }

        if ($amount < ChannelLimits::PAYOUT_MOBILE_MIN) {
            return (float) ($slabs[0]['fee'] ?? 52.0);
        }

        return (float) ($slabs[array_key_last($slabs)]['fee'] ?? 9890.0);
    }

    /**
     * Calculate Bank payout fee.
     * EFT: flat 2,360 TZS (up to 20M TZS).
     * TISS TZS: flat 11,800 TZS (up to 1B TZS).
     * TISS USD: flat $7.50 USD (up to $1M USD).
     */
    public function calculateBankPayoutFee(
        float $amount,
        string $transferType = 'auto',
        string $currency = 'TZS'
    ): float {
        $currency = strtoupper($currency);

        if ($currency === 'USD') {
            return (float) ($this->pricing['bank_tiss_usd_fee'] ?? 7.50);
        }

        $type = strtoupper($transferType);
        if ($type === 'AUTO') {
            $threshold = (float) ($this->pricing['bank_eft_max_threshold'] ?? ChannelLimits::BANK_EFT_MAX);
            $type = ChannelLimits::recommendBankTransferMethod($amount, $currency, $threshold);
        }

        return match ($type) {
            'TISS' => (float) ($this->pricing['bank_tiss_tzs_fee'] ?? 11800.0),
            default => (float) ($this->pricing['bank_eft_fee'] ?? 2360.0),
        };
    }

    /**
     * Calculate net merchant settlement after deducting merchant-borne fee.
     */
    public function calculateNetSettlement(string $channel, float $grossAmount): float
    {
        $normalized = strtolower(str_replace(['-', ' '], '_', $channel));

        $fee = match ($normalized) {
            'billpay_halopesa' => $this->calculateBillPayFee('halopesa', $grossAmount),
            'billpay_mixx', 'billpay_tigo' => $this->calculateBillPayFee('mixx', $grossAmount),
            'billpay_crdb' => $this->calculateBillPayFee('crdb', $grossAmount),
            'billpay', 'billpay_mpesa', 'billpay_airtel' => $this->calculateBillPayFee('mpesa', $grossAmount),
            'crdb_direct_debit' => $this->calculateCrdbDirectDebitFee(),
            'tanqr_collection', 'lipa_namba_collection', 'tanqr', 'lipa_namba' => $this->calculateTanQrCollectionFee($grossAmount),
            default => 0.0,
        };

        return max(0.0, round($grossAmount - $fee, 2));
    }

    /**
     * Calculate payout budgeting: whether merchant absorbs fee on top or deducts it from recipient.
     *
     * @return array{
     *     payout_type: string,
     *     target_amount: float,
     *     fee: float,
     *     absorb_fee: bool,
     *     total_debited_from_merchant: float,
     *     net_received_by_recipient: float,
     *     currency: string
     * }
     */
    public function calculatePayoutDeduction(
        string $payoutType,
        float $targetAmount,
        bool $absorbFee = true,
        string $currency = 'TZS'
    ): array {
        $normalized = strtolower(str_replace(['-', ' '], '_', $payoutType));

        $fee = match ($normalized) {
            'bank', 'bank_payout', 'eft', 'tiss' => $this->calculateBankPayoutFee($targetAmount, $payoutType, $currency),
            default => $this->calculatePayoutFee($targetAmount),
        };

        if ($absorbFee) {
            // Business absorbs fee: Recipient gets full target amount; merchant pays amount + fee.
            return [
                'payout_type' => $payoutType,
                'target_amount' => $targetAmount,
                'fee' => $fee,
                'absorb_fee' => true,
                'total_debited_from_merchant' => round($targetAmount + $fee, 2),
                'net_received_by_recipient' => $targetAmount,
                'currency' => strtoupper($currency),
            ];
        }

        // Business deducts fee from recipient: merchant pays targetAmount; recipient receives targetAmount - fee.
        return [
            'payout_type' => $payoutType,
            'target_amount' => $targetAmount,
            'fee' => $fee,
            'absorb_fee' => false,
            'total_debited_from_merchant' => $targetAmount,
            'net_received_by_recipient' => max(0.0, round($targetAmount - $fee, 2)),
            'currency' => strtoupper($currency),
        ];
    }

    /**
     * Get a comprehensive breakdown for any channel including who pays and totals.
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function getBreakdown(string $channel, float $amount, array $options = []): array
    {
        $currency = strtoupper((string) ($options['currency'] ?? 'TZS'));
        $bearer = FeeBearer::forChannel($channel);
        $normalized = strtolower(str_replace(['-', ' '], '_', $channel));

        $fee = match ($normalized) {
            'ussd_push' => $this->calculateUssdPushFee($amount),
            'card', 'card_payment' => $this->calculateCardFee($amount),
            'billpay_halopesa' => $this->calculateBillPayFee('halopesa', $amount),
            'billpay_mixx', 'billpay_tigo' => $this->calculateBillPayFee('mixx', $amount),
            'billpay_crdb' => $this->calculateBillPayFee('crdb', $amount),
            'billpay', 'billpay_mpesa', 'billpay_airtel' => $this->calculateBillPayFee('mpesa', $amount),
            'crdb_direct_debit' => $this->calculateCrdbDirectDebitFee(),
            'tanqr_collection', 'lipa_namba_collection' => $this->calculateTanQrCollectionFee($amount),
            'bank_payout', 'payout_bank' => $this->calculateBankPayoutFee($amount, $options['transfer_type'] ?? 'auto', $currency),
            'mobile_money_payout', 'payout_mobile', 'tanqr_payout', 'payout_tanqr', 'lipa_namba_payout', 'payout_lipa_namba' => $this->calculatePayoutFee($amount),
            default => 0.0,
        };

        $result = [
            'channel' => $channel,
            'amount' => $amount,
            'fee' => $fee,
            'fee_bearer' => $bearer,
            'currency' => $currency,
        ];

        if ($bearer === FeeBearer::CUSTOMER) {
            $result['total_charged_to_customer'] = round($amount + $fee, 2);
            $result['merchant_receives'] = $amount;
        } elseif ($bearer === FeeBearer::MERCHANT) {
            $result['total_charged_to_customer'] = $amount;
            $result['net_merchant_settlement'] = max(0.0, round($amount - $fee, 2));
        } else {
            // Payout (configurable)
            $absorb = (bool) ($options['absorb_fee'] ?? true);
            $deduction = $this->calculatePayoutDeduction($channel, $amount, $absorb, $currency);
            $result['absorb_fee'] = $absorb;
            $result['total_debited_from_merchant'] = $deduction['total_debited_from_merchant'];
            $result['net_received_by_recipient'] = $deduction['net_received_by_recipient'];
        }

        return $result;
    }
}
