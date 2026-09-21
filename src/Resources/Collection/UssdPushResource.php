<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Collection;

use ClickPesa\Resources\AbstractResource;

class UssdPushResource extends AbstractResource
{
    /**
     * Validates push details like phone number, amount, order-reference and verifies payment channels availability.
     *
     * @param array{
     *     amount: string|numeric,
     *     currency: string,
     *     orderReference: string,
     *     phoneNumber?: string,
     *     fetchSenderDetails?: bool,
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function preview(array $data): array
    {
        return $this->requestPost('/payments/preview-ussd-push-request', $data);
    }

    /**
     * Sends the USSD-PUSH request to customer's mobile device for payment authorization.
     *
     * @param array{
     *     amount: string|numeric,
     *     currency: string,
     *     orderReference: string,
     *     phoneNumber: string,
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function initiate(array $data): array
    {
        return $this->requestPost('/payments/initiate-ussd-push-request', $data);
    }
}
