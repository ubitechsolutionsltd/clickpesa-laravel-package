<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Collection;

use ClickPesa\Resources\AbstractResource;

class PaymentStatusResource extends AbstractResource
{
    /**
     * Queries for the payment status using payment's Order Reference.
     * Note: Returns an array of payment attempts for the given order reference.
     *
     * @param string $orderReference
     * @return array<int, array<string, mixed>>
     */
    public function get(string $orderReference): array
    {
        return $this->requestGet('/payments/' . rawurlencode($orderReference));
    }

    /**
     * Query all payments with filtering, sorting, and pagination capabilities.
     *
     * @param array{
     *     startDate?: string,
     *     endDate?: string,
     *     status?: 'SUCCESS'|'SETTLED'|'PROCESSING'|'PENDING'|'FAILED',
     *     collectedCurrency?: string,
     *     channel?: string,
     *     orderReference?: string,
     *     clientId?: string,
     *     sortBy?: string,
     *     orderBy?: 'ASC'|'DESC',
     *     skip?: int,
     *     limit?: int
     * } $filters
     * @return array{data: array<int, array<string, mixed>>, totalCount: int}
     */
    public function all(array $filters = []): array
    {
        return $this->requestGet('/payments/all', $filters);
    }
}
