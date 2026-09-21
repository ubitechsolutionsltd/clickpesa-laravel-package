<?php

declare(strict_types=1);

namespace ClickPesa\Webhooks\Events;

class PayoutRefundedEvent extends WebhookEvent
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

    public function getRefund(): ?array
    {
        return $this->data['refund'] ?? null;
    }

    public function getRefundMessage(): ?string
    {
        return $this->data['refund']['message'] ?? null;
    }
}
