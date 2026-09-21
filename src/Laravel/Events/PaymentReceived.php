<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Events;

use ClickPesa\Webhooks\Events\PaymentReceivedEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable;
    use SerializesModels;

    public PaymentReceivedEvent $event;

    public function __construct(PaymentReceivedEvent $event)
    {
        $this->event = $event;
    }

    public function getOrderReference(): ?string
    {
        return $this->event->getOrderReference();
    }

    public function getPaymentReference(): ?string
    {
        return $this->event->getPaymentReference();
    }

    public function getCollectedAmount(): ?string
    {
        return $this->event->getCollectedAmount();
    }

    public function getCollectedCurrency(): ?string
    {
        return $this->event->getCollectedCurrency();
    }

    public function getCustomer(): ?array
    {
        return $this->event->getCustomer();
    }
}
