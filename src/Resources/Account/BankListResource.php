<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Account;

use ClickPesa\Resources\AbstractResource;

class BankListResource extends AbstractResource
{
    /**
     * Get list of supported banks in Tanzania with their BICs.
     *
     * @return array<int, array{name: string, bic: string}>
     */
    public function all(): array
    {
        return $this->requestGet('/list/banks');
    }
}
