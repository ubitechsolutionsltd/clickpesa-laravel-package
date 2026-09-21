<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Events;

use ClickPesa\Webhooks\Events\PayoutReversedEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayoutReversed
{
    use Dispatchable;
    use SerializesModels;

    public PayoutReversedEvent $event;

    public function __construct(PayoutReversedEvent $event)
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

    public function getReverseMessage(): ?string
    {
        return $this->event->getReverseMessage();
    }
}
