<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Disbursement;

use ClickPesa\Resources\AbstractResource;

class PayoutLinkResource extends AbstractResource
{
    /**
     * Generate Payout Link for making payouts.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function generate(array $data): array
    {
        return $this->requestPost('/payout-link/generate-payout-url', $data);
    }

    /**
     * Alias for generate().
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->generate($data);
    }
}
