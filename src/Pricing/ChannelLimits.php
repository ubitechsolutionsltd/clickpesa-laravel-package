<?php

declare(strict_types=1);

namespace ClickPesa\Pricing;

use ClickPesa\Exceptions\ValidationException;

class ChannelLimits
{
    // Transaction limits in TZS unless specified
    public const USSD_PUSH_MIN = 500.0;
    public const USSD_PUSH_MAX = 3_000_000.0;

    public const BILLPAY_MOBILE_MIN = 500.0;
    public const BILLPAY_MOBILE_MAX = 5_000_000.0;

    public const BILLPAY_CRDB_MIN = 1_000.0;
    public const BILLPAY_CRDB_MAX = 100_000_000.0;

    public const CRDB_DIRECT_DEBIT_MAX = 100_000_000.0;

    public const PAYOUT_MOBILE_MIN = 100.0;
    public const PAYOUT_MOBILE_MAX = 5_000_000.0;

    public const PAYOUT_TANQR_MIN = 100.0;
    public const PAYOUT_TANQR_MAX = 5_000_000.0;

    public const BANK_EFT_MAX = 20_000_000.0;
    public const BANK_TISS_TZS_MAX = 1_000_000_000.0;
    public const BANK_TISS_USD_MAX = 1_000_000.0;

    public const PRE_KYC_ACCOUNT_LIMIT = 100_000.0;

    /**
     * Get limits array for a given channel.
     *
     * @return array{min: float, max: float, currency: string}
     */
    public static function get(string $channel, string $currency = 'TZS'): array
    {
        $normalized = strtolower(str_replace(['-', ' '], '_', $channel));

        return match ($normalized) {
            'ussd_push' => [
                'min' => self::USSD_PUSH_MIN,
                'max' => self::USSD_PUSH_MAX,
                'currency' => 'TZS',
            ],
            'billpay_mobile', 'billpay_mpesa', 'billpay_airtel', 'billpay_halopesa', 'billpay_mixx' => [
                'min' => self::BILLPAY_MOBILE_MIN,
                'max' => self::BILLPAY_MOBILE_MAX,
                'currency' => 'TZS',
            ],
            'billpay_crdb' => [
                'min' => self::BILLPAY_CRDB_MIN,
                'max' => self::BILLPAY_CRDB_MAX,
                'currency' => 'TZS',
            ],
            'crdb_direct_debit' => [
                'min' => 0.0,
                'max' => self::CRDB_DIRECT_DEBIT_MAX,
                'currency' => 'TZS',
            ],
            'payout_mobile', 'payout_mobile_money' => [
                'min' => self::PAYOUT_MOBILE_MIN,
                'max' => self::PAYOUT_MOBILE_MAX,
                'currency' => 'TZS',
            ],
            'payout_tanqr', 'payout_lipa_namba' => [
                'min' => self::PAYOUT_TANQR_MIN,
                'max' => self::PAYOUT_TANQR_MAX,
                'currency' => 'TZS',
            ],
            'bank_eft' => [
                'min' => 0.0,
                'max' => self::BANK_EFT_MAX,
                'currency' => 'TZS',
            ],
            'bank_tiss' => [
                'min' => 0.0,
                'max' => strtoupper($currency) === 'USD' ? self::BANK_TISS_USD_MAX : self::BANK_TISS_TZS_MAX,
                'currency' => strtoupper($currency),
            ],
            default => [
                'min' => 0.0,
                'max' => PHP_FLOAT_MAX,
                'currency' => strtoupper($currency),
            ],
        };
    }

    /**
     * Validates if the given transaction amount is within ClickPesa's official channel limits.
     *
     * @throws ValidationException
     */
    public static function validate(string $channel, float $amount, string $currency = 'TZS'): void
    {
        $limits = self::get($channel, $currency);

        if ($amount < $limits['min']) {
            throw new ValidationException(sprintf(
                'Amount %s %s is below minimum allowed limit (%s %s) for channel "%s".',
                number_format($amount, 2),
                $limits['currency'],
                number_format($limits['min'], 2),
                $limits['currency'],
                $channel
            ));
        }

        if ($amount > $limits['max']) {
            throw new ValidationException(sprintf(
                'Amount %s %s exceeds maximum allowed limit (%s %s) for channel "%s".',
                number_format($amount, 2),
                $limits['currency'],
                number_format($limits['max'], 2),
                $limits['currency'],
                $channel
            ));
        }
    }

    /**
     * Recommends whether a bank payout should be routed via EFT or TISS based on amount and threshold.
     */
    public static function recommendBankTransferMethod(
        float $amount,
        string $currency = 'TZS',
        float $eftThreshold = self::BANK_EFT_MAX
    ): string {
        if (strtoupper($currency) === 'USD') {
            return 'TISS'; // USD transfers are routed via TISS
        }

        return $amount > $eftThreshold ? 'TISS' : 'EFT';
    }
}
