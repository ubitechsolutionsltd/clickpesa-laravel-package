<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Disbursement;

use ClickPesa\Resources\AbstractResource;

class BankPayoutResource extends AbstractResource
{
    /**
     * Validates a bank payout and returns the fee, balance, and estimated arrival.
     *
     * @param array{
     *     amount: float|int|numeric,
     *     orderReference: string,
     *     accountNumber: string,
     *     currency: 'TZS'|'USD',
     *     bic: string,
     *     accountName?: string,
     *     transferType?: 'ACH'|'RTGS',
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function preview(array $data): array
    {
        return $this->requestPost('/payouts/preview-bank-payout', $data);
    }

    /**
     * Sends a payout to a bank account.
     * Note: Subject to 60 seconds cooldown between payout creations.
     *
     * @param array{
     *     amount: float|int|numeric,
     *     orderReference: string,
     *     accountNumber: string,
     *     accountName: string,
     *     currency: 'TZS'|'USD',
     *     bic: string,
     *     accountCurrency?: string,
     *     transferType?: 'ACH'|'RTGS',
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->requestPost('/payouts/create-bank-payout', $data);
    }
}
