<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Events;

use ClickPesa\Webhooks\Events\PaymentFailedEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable;
    use SerializesModels;

    public PaymentFailedEvent $event;

    public function __construct(PaymentFailedEvent $event)
    {
        $this->event = $event;
    }

    public function getOrderReference(): ?string
    {
        return $this->event->getOrderReference();
    }

    public function getMessage(): ?string
    {
        return $this->event->getMessage();
    }
}
