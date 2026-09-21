<?php

declare(strict_types=1);

namespace ClickPesa\Webhooks;

use ClickPesa\Exceptions\WebhookVerificationException;
use ClickPesa\Security\Checksum;
use ClickPesa\Webhooks\Events\DepositReceivedEvent;
use ClickPesa\Webhooks\Events\GenericWebhookEvent;
use ClickPesa\Webhooks\Events\PaymentFailedEvent;
use ClickPesa\Webhooks\Events\PaymentReceivedEvent;
use ClickPesa\Webhooks\Events\PayoutInitiatedEvent;
use ClickPesa\Webhooks\Events\PayoutRefundedEvent;
use ClickPesa\Webhooks\Events\PayoutReversedEvent;
use ClickPesa\Webhooks\Events\WebhookEvent;
use InvalidArgumentException;

class WebhookHandler
{
    private ?string $checksumKey;

    public function __construct(?string $checksumKey = null)
    {
        $this->checksumKey = $checksumKey;
    }

    /**
     * Parse webhook payload and return typed WebhookEvent instance.
     * Optionally verifies checksum if $checksumKey is configured.
     *
     * @param string|array<string, mixed> $payload
     * @param string|null $receivedChecksum
     * @return WebhookEvent
     * @throws WebhookVerificationException|InvalidArgumentException
     */
    public function handle(string|array $payload, ?string $receivedChecksum = null): WebhookEvent
    {
        $data = is_string($payload) ? json_decode($payload, true) : $payload;

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON webhook payload.');
        }

        // Checksum verification
        if (!empty($this->checksumKey)) {
            $checksumToVerify = $receivedChecksum ?? ($data['checksum'] ?? null);

            if (empty($checksumToVerify)) {
                throw new WebhookVerificationException('Missing checksum for webhook verification.');
            }

            if (!Checksum::verify($this->checksumKey, $data, (string) $checksumToVerify)) {
                throw new WebhookVerificationException('Invalid webhook checksum.');
            }
        }

        $eventName = (string) ($data['event'] ?? 'UNKNOWN');
        $eventData = (array) ($data['data'] ?? []);

        return match ($eventName) {
            'PAYMENT RECEIVED' => new PaymentReceivedEvent($eventName, $eventData, $data),
            'PAYMENT FAILED' => new PaymentFailedEvent($eventName, $eventData, $data),
            'PAYOUT INITIATED' => new PayoutInitiatedEvent($eventName, $eventData, $data),
            'PAYOUT REFUNDED' => new PayoutRefundedEvent($eventName, $eventData, $data),
            'PAYOUT REVERSED' => new PayoutReversedEvent($eventName, $eventData, $data),
            'DEPOSIT RECEIVED' => new DepositReceivedEvent($eventName, $eventData, $data),
            default => new GenericWebhookEvent($eventName, $eventData, $data),
        };
    }
}
