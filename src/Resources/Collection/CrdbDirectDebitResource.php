<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Collection;

use ClickPesa\Resources\AbstractResource;

class CrdbDirectDebitResource extends AbstractResource
{
    /**
     * Request CRDB Direct Debit mandate approval and create the linked payment schedule.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function requestMandate(array $data): array
    {
        return $this->requestPost('/crdb-direct-debit/request-mandate', $data);
    }

    /**
     * Get CRDB Direct Debit mandate details and linked payment plan summary by mandate request ID.
     *
     * @param string $mandateRequestId
     * @return array<string, mixed>
     */
    public function getMandate(string $mandateRequestId): array
    {
        return $this->requestGet('/crdb-direct-debit/get-mandate/' . rawurlencode($mandateRequestId));
    }

    /**
     * Cancel an ACTIVE CRDB Direct Debit mandate.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function cancelMandate(array $data): array
    {
        return $this->requestPost('/crdb-direct-debit/cancel-mandate', $data);
    }
}
