<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Events;

use ClickPesa\Webhooks\Events\PayoutInitiatedEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayoutInitiated
{
    use Dispatchable;
    use SerializesModels;

    public PayoutInitiatedEvent $event;

    public function __construct(PayoutInitiatedEvent $event)
    {
        $this->event = $event;
    }

    public function getOrderReference(): ?string
    {
        return $this->event->getOrderReference();
    }

    public function getAmount(): ?string
    {
        return $this->event->getAmount();
    }

    public function getCurrency(): ?string
    {
        return $this->event->getCurrency();
    }
}
