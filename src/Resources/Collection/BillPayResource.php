<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Collection;

use ClickPesa\Resources\AbstractResource;

class BillPayResource extends AbstractResource
{
    /**
     * Generates a BillPay Control Number for a specific bill, transaction or invoice.
     *
     * @param array{
     *     billDescription?: string,
     *     billPaymentMode?: 'ALLOW_PARTIAL_AND_OVER_PAYMENT'|'EXACT',
     *     billAmount?: float|int,
     *     billMaxCollectionAmount?: float|int,
     *     billReference?: string
     * } $data
     * @return array<string, mixed>
     */
    public function createOrder(array $data = []): array
    {
        return $this->requestPost('/billpay/create-order-control-number', $data);
    }

    /**
     * Generates a BillPay Control Number for a specific customer.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createCustomer(array $data = []): array
    {
        return $this->requestPost('/billpay/create-customer-control-number', $data);
    }

    /**
     * Bulk create BillPay Order Control Numbers (max 50 per request).
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<string, mixed>
     */
    public function bulkCreateOrders(array $items): array
    {
        return $this->requestPost('/billpay/bulk-create-order-control-numbers', $items);
    }

    /**
     * Bulk create BillPay Customer Control Numbers (max 50 per request).
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<string, mixed>
     */
    public function bulkCreateCustomers(array $items): array
    {
        return $this->requestPost('/billpay/bulk-create-customer-control-numbers', $items);
    }

    /**
     * Queries for BillPay number details.
     *
     * @param string $billPayNumber
     * @return array<string, mixed>
     */
    public function get(string $billPayNumber): array
    {
        return $this->requestGet('/billpay/' . rawurlencode($billPayNumber));
    }

    /**
     * Partially updates a BillPay reference.
     *
     * @param string $billPayNumber
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $billPayNumber, array $data): array
    {
        return $this->requestPatch('/billpay/' . rawurlencode($billPayNumber), $data);
    }
}
