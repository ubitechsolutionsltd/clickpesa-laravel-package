<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Events;

use ClickPesa\Webhooks\Events\PayoutRefundedEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayoutRefunded
{
    use Dispatchable;
    use SerializesModels;

    public PayoutRefundedEvent $event;

    public function __construct(PayoutRefundedEvent $event)
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

    public function getRefundMessage(): ?string
    {
        return $this->event->getRefundMessage();
    }
}
