<?php

declare(strict_types=1);

namespace ClickPesa\Webhooks\Events;

class DepositReceivedEvent extends WebhookEvent
{
    public function getPaymentReference(): ?string
    {
        return $this->data['paymentReference'] ?? null;
    }

    public function getDepositAmount(): ?string
    {
        return isset($this->data['depositAmount']) ? (string) $this->data['depositAmount'] : null;
    }

    public function getDepositCurrency(): ?string
    {
        return $this->data['depositCurrency'] ?? null;
    }
}
