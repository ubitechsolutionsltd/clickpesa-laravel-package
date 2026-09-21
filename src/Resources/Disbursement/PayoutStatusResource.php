<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Disbursement;

use ClickPesa\Resources\AbstractResource;

class PayoutStatusResource extends AbstractResource
{
    /**
     * Queries for the payout status using payout's Order Reference.
     *
     * @param string $orderReference
     * @return array<string, mixed>
     */
    public function get(string $orderReference): array
    {
        return $this->requestGet('/payouts/' . rawurlencode($orderReference));
    }

    /**
     * Query all payouts with filtering, sorting, and pagination capabilities.
     *
     * @param array<string, mixed> $filters
     * @return array{data: array<int, array<string, mixed>>, totalCount: int}
     */
    public function all(array $filters = []): array
    {
        return $this->requestGet('/payouts/all', $filters);
    }
}
