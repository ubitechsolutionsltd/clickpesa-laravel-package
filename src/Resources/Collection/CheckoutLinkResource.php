<?php

declare(strict_types=1);

namespace ClickPesa\Resources\Collection;

use ClickPesa\Resources\AbstractResource;

class CheckoutLinkResource extends AbstractResource
{
    /**
     * Generates a link for receiving payments via Hosted Checkout.
     *
     * @param array{
     *     orderReference: string,
     *     orderCurrency: string,
     *     totalPrice?: string|numeric,
     *     orderItems?: array<array{name: string, price: string|numeric, quantity: int}>,
     *     customerName?: string,
     *     customerEmail?: string,
     *     customerPhone?: string,
     *     description?: string,
     *     callbackUrl?: string,
     *     wooCommerceCallbackURL?: string,
     *     checksum?: string
     * } $data
     * @return array<string, mixed>
     */
    public function generate(array $data): array
    {
        return $this->requestPost('/checkout-link/generate-checkout-url', $data);
    }
}
