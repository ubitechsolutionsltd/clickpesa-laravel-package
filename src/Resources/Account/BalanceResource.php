<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Account;

use ClickPesa\Exceptions\NotFoundException;
use ClickPesa\Resources\AbstractResource;

class BalanceResource extends AbstractResource
{
    /**
     * Get merchant account balances across currencies (e.g. TZS, USD).
     * If no balance account exists yet (pre-transaction), ClickPesa returns 404;
     * this method can either return an empty array or throw, depending on $suppressNotFound.
     *
     * @param bool $suppressNotFound If true, returns empty array instead of throwing NotFoundException
     * @return array<int, array{currency: string, balance: float|int}>
     * @throws NotFoundException
     */
    public function get(bool $suppressNotFound = true): array
    {
        try {
            return $this->requestGet('/account/balance');
        } catch (NotFoundException $e) {
            if ($suppressNotFound) {
                return [];
            }
            throw $e;
        }
    }

    /**
     * Get merchant account statement.
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function statement(array $query = []): array
    {
        return $this->requestGet('/account/statement', $query);
    }
}
