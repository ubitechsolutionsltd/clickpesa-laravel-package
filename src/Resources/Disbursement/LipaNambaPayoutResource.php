<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Disbursement;

use ClickPesa\Resources\AbstractResource;

class LipaNambaPayoutResource extends AbstractResource
{
    /**
     * Returns the list of Lipa Namba providers.
     *
     * @return array<int, array{name: string, providerCode: string}>
     */
    public function providers(): array
    {
        return $this->requestGet('/payouts/lipa-namba-providers');
    }

    /**
     * Validates a Lipa Namba or TanQR payout and returns the fee, balance, and beneficiary name.
     *
     * @param array{
     *     amount: float|int|numeric,
     *     orderReference: string,
     *     currency: 'TZS'|'USD',
     *     lipaNamba?: string,
     *     providerCode?: string,
     *     qrCode?: string,
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function preview(array $data): array
    {
        return $this->requestPost('/payouts/preview-lipa-namba-payout', $data);
    }

    /**
     * Sends a payout to a Lipa Namba or TanQR.
     * Note: Subject to 60 seconds cooldown between payout creations.
     *
     * @param array{
     *     amount: float|int|numeric,
     *     orderReference: string,
     *     currency: 'TZS'|'USD',
     *     lipaNamba?: string,
     *     providerCode?: string,
     *     qrCode?: string,
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->requestPost('/payouts/create-lipa-namba-payout', $data);
    }
}
