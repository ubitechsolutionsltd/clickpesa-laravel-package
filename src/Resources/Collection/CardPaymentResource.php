<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Collection;

use ClickPesa\Resources\AbstractResource;

class CardPaymentResource extends AbstractResource
{
    /**
     * Validates card payment details like Amount, Order Reference and verifies Method availability.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function preview(array $data): array
    {
        return $this->requestPost('/payments/preview-card-payment', $data);
    }

    /**
     * Initiates card payment. Returns a link for the customer to complete payment.
     *
     * @param array{
     *     amount: string|numeric,
     *     orderReference: string,
     *     currency: string,
     *     customer: array{id: string}|array{fullName: string, email: string, phoneNumber: string},
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function initiate(array $data): array
    {
        return $this->requestPost('/payments/initiate-card-payment', $data);
    }
}
