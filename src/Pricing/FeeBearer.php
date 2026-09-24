<?php

declare(strict_types=1);

namespace ClickPesa\Pricing;

class FeeBearer
{
    public const CUSTOMER = 'customer';
    public const MERCHANT = 'merchant';
    public const CONFIGURABLE = 'configurable';

    /**
     * Determines whether the fee for a given channel is paid by customer, merchant, or is configurable.
     */
    public static function forChannel(string $channel): string
    {
        $normalized = strtolower(str_replace(['-', ' '], '_', $channel));

        return match ($normalized) {
            'ussd_push', 'card', 'card_payment' => self::CUSTOMER,
            'billpay', 'billpay_mpesa', 'billpay_airtel', 'billpay_halopesa',
            'billpay_mixx', 'billpay_crdb', 'crdb_direct_debit',
            'tanqr_collection', 'lipa_namba_collection' => self::MERCHANT,
            'payout', 'payout_mobile_money', 'payout_bank', 'payout_tanqr',
            'payout_lipa_namba', 'payout_link' => self::CONFIGURABLE,
            default => self::MERCHANT,
        };
    }
}
