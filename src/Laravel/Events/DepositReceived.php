<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Events;

use ClickPesa\Webhooks\Events\DepositReceivedEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepositReceived
{
    use Dispatchable;
    use SerializesModels;

    public DepositReceivedEvent $event;

    public function __construct(DepositReceivedEvent $event)
    {
        $this->event = $event;
    }

    public function getPaymentReference(): ?string
    {
        return $this->event->getPaymentReference();
    }

    public function getDepositAmount(): ?string
    {
        return $this->event->getDepositAmount();
    }

    public function getDepositCurrency(): ?string
    {
        return $this->event->getDepositCurrency();
    }
}
