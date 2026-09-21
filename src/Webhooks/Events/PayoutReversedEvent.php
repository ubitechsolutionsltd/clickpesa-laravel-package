<?php

declare(strict_types=1);

namespace ClickPesa\Webhooks\Events;

class PayoutReversedEvent extends WebhookEvent
{
    public function getAmount(): ?string
    {
        return isset($this->data['amount']) ? (string) $this->data['amount'] : null;
    }

    public function getCurrency(): ?string
    {
        return $this->data['currency'] ?? null;
    }

    public function getFee(): ?string
    {
        return isset($this->data['fee']) ? (string) $this->data['fee'] : null;
    }

    public function getReverse(): ?array
    {
        return $this->data['reverse'] ?? null;
    }

    public function getReverseMessage(): ?string
    {
        return $this->data['reverse']['message'] ?? null;
    }

    public function isReversedWithFee(): bool
    {
        return (bool) ($this->data['reverse']['reversedWithFee'] ?? false);
    }
}
