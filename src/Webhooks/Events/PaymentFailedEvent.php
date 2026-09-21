<?php

declare(strict_types=1);

namespace ClickPesa\Webhooks\Events;

class PaymentFailedEvent extends WebhookEvent
{
    public function getClientId(): ?string
    {
        return $this->data['clientId'] ?? null;
    }
}
