<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Disbursement;

use ClickPesa\Resources\AbstractResource;

class MobileMoneyPayoutResource extends AbstractResource
{
    /**
     * Validates a mobile money payout and returns the fee and balance.
     *
     * @param array{
     *     amount: float|int|numeric,
     *     currency: 'TZS'|'USD',
     *     orderReference: string,
     *     phoneNumber: string,
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function preview(array $data): array
    {
        return $this->requestPost('/payouts/preview-mobile-money-payout', $data);
    }

    /**
     * Sends a payout to a mobile money wallet.
     * Note: Subject to 60 seconds cooldown between payout creations.
     *
     * @param array{
     *     amount: float|int|numeric,
     *     currency: 'TZS'|'USD',
     *     orderReference: string,
     *     phoneNumber: string,
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->requestPost('/payouts/create-mobile-money-payout', $data);
    }
}
