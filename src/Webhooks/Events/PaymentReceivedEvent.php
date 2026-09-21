<?php

declare(strict_types=1);

namespace ClickPesa\Webhooks\Events;

class PaymentReceivedEvent extends WebhookEvent
{
    public function getPaymentReference(): ?string
    {
        return $this->data['paymentReference'] ?? null;
    }

    public function getCollectedAmount(): ?string
    {
        return isset($this->data['collectedAmount']) ? (string) $this->data['collectedAmount'] : null;
    }

    public function getCollectedCurrency(): ?string
    {
        return $this->data['collectedCurrency'] ?? null;
    }

    public function getCustomer(): ?array
    {
        return $this->data['customer'] ?? null;
    }

    public function getCustomerName(): ?string
    {
        return $this->data['customer']['customerName'] ?? null;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->data['customer']['customerEmail'] ?? null;
    }

    public function getCustomerPhoneNumber(): ?string
    {
        return $this->data['customer']['customerPhoneNumber'] ?? null;
    }
}
