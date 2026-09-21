<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Account;

use ClickPesa\Resources\AbstractResource;

class ExchangeRateResource extends AbstractResource
{
    /**
     * Retrieve latest exchange rates between currencies.
     *
     * @param array{
     *     source?: string,
     *     target?: string
     * } $query
     * @return array<int, array{source: string, target: string, rate: float, date: string}>
     */
    public function all(array $query = []): array
    {
        return $this->requestGet('/exchange-rates/all', $query);
    }
}
