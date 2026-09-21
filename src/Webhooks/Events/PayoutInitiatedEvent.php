<?php

declare(strict_types=1);

namespace ClickPesa\Webhooks\Events;

class PayoutInitiatedEvent extends WebhookEvent
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

    public function getChannelProvider(): ?string
    {
        return $this->data['channelProvider'] ?? null;
    }

    public function getTransferType(): ?string
    {
        return $this->data['transferType'] ?? null;
    }

    public function getBeneficiary(): ?array
    {
        return $this->data['beneficiary'] ?? null;
    }
}
