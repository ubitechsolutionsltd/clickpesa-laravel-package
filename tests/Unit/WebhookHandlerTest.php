<?php

declare(strict_types=1);

namespace ClickPesa\Tests\Unit;

use ClickPesa\Exceptions\WebhookVerificationException;
use ClickPesa\Security\Checksum;
use ClickPesa\Webhooks\Events\DepositReceivedEvent;
use ClickPesa\Webhooks\Events\PaymentFailedEvent;
use ClickPesa\Webhooks\Events\PaymentReceivedEvent;
use ClickPesa\Webhooks\Events\PayoutInitiatedEvent;
use ClickPesa\Webhooks\Events\PayoutRefundedEvent;
use ClickPesa\Webhooks\Events\PayoutReversedEvent;
use ClickPesa\Webhooks\WebhookHandler;
use PHPUnit\Framework\TestCase;

class WebhookHandlerTest extends TestCase
{
    private string $secretKey = 'test_webhook_secret';

    public function test_it_parses_payment_received_event(): void
    {
        $payload = [
            'event' => 'PAYMENT RECEIVED',
            'data' => [
                'id' => 'ORD123456LCP7890',
                'status' => 'SUCCESS',
                'paymentReference' => 'abc123def456ghi789',
                'orderReference' => 'ORD123456',
                'collectedAmount' => '10000',
                'collectedCurrency' => 'TZS',
                'message' => 'success',
                'channel' => 'CRDB DIRECT DEBIT MANDATE',
                'customer' => [
                    'customerName' => 'John Doe',
                    'customerEmail' => 'john@example.com',
                    'customerPhoneNumber' => '255700000000',
                ],
            ],
        ];

        $checksum = Checksum::generate($this->secretKey, $payload);
        $payload['checksum'] = $checksum;

        $handler = new WebhookHandler($this->secretKey);
        $event = $handler->handle($payload);

        $this->assertInstanceOf(PaymentReceivedEvent::class, $event);
        $this->assertSame('ORD123456', $event->getOrderReference());
        $this->assertSame('10000', $event->getCollectedAmount());
        $this->assertSame('TZS', $event->getCollectedCurrency());
        $this->assertSame('John Doe', $event->getCustomerName());
        $this->assertSame('john@example.com', $event->getCustomerEmail());
        $this->assertSame('255700000000', $event->getCustomerPhoneNumber());
    }

    public function test_it_parses_payment_failed_event(): void
    {
        $payload = [
            'event' => 'PAYMENT FAILED',
            'data' => [
                'id' => 'ORD123456LCP7890',
                'status' => 'FAILED',
                'channel' => 'CRDB DIRECT DEBIT MANDATE',
                'orderReference' => 'ORD123456',
                'message' => 'Insufficient balance',
                'clientId' => 'ID1234XHYAJK',
            ],
        ];

        $checksum = Checksum::generate($this->secretKey, $payload);
        $handler = new WebhookHandler($this->secretKey);
        $event = $handler->handle($payload, $checksum);

        $this->assertInstanceOf(PaymentFailedEvent::class, $event);
        $this->assertSame('ORD123456', $event->getOrderReference());
        $this->assertSame('FAILED', $event->getStatus());
        $this->assertSame('Insufficient balance', $event->getMessage());
        $this->assertSame('ID1234XHYAJK', $event->getClientId());
    }

    public function test_it_parses_payout_events(): void
    {
        $handler = new WebhookHandler();

        // Payout Initiated
        $initiated = $handler->handle([
            'event' => 'PAYOUT INITIATED',
            'data' => [
                'orderReference' => 'payout_1',
                'amount' => '20000.00',
                'currency' => 'TZS',
                'fee' => '2360.00',
                'status' => 'SUCCESS',
                'channel' => 'BANK TRANSFER',
                'channelProvider' => 'Equity Bank Tanzania Limited',
            ],
        ]);
        $this->assertInstanceOf(PayoutInitiatedEvent::class, $initiated);
        $this->assertSame('20000.00', $initiated->getAmount());

        // Payout Refunded
        $refunded = $handler->handle([
            'event' => 'PAYOUT REFUNDED',
            'data' => [
                'orderReference' => 'payout_2',
                'amount' => '1000.00',
                'refund' => ['message' => 'Insufficient balance'],
            ],
        ]);
        $this->assertInstanceOf(PayoutRefundedEvent::class, $refunded);
        $this->assertSame('Insufficient balance', $refunded->getRefundMessage());

        // Payout Reversed
        $reversed = $handler->handle([
            'event' => 'PAYOUT REVERSED',
            'data' => [
                'orderReference' => 'payout_3',
                'amount' => '5000.00',
                'reverse' => [
                    'message' => 'Invalid account',
                    'reversedWithFee' => true,
                ],
            ],
        ]);
        $this->assertInstanceOf(PayoutReversedEvent::class, $reversed);
        $this->assertTrue($reversed->isReversedWithFee());

        // Deposit Received
        $deposit = $handler->handle([
            'event' => 'DEPOSIT RECEIVED',
            'data' => [
                'depositAmount' => '50000',
                'depositCurrency' => 'TZS',
                'paymentReference' => 'DEP999',
            ],
        ]);
        $this->assertInstanceOf(DepositReceivedEvent::class, $deposit);
        $this->assertSame('50000', $deposit->getDepositAmount());
    }

    public function test_it_throws_exception_on_invalid_checksum(): void
    {
        $this->expectException(WebhookVerificationException::class);
        $this->expectExceptionMessage('Invalid webhook checksum.');

        $handler = new WebhookHandler($this->secretKey);
        $handler->handle(['event' => 'PAYMENT RECEIVED', 'data' => []], 'invalid_hash');
    }

    public function test_it_throws_exception_on_missing_checksum_when_key_configured(): void
    {
        $this->expectException(WebhookVerificationException::class);
        $this->expectExceptionMessage('Missing checksum for webhook verification.');

        $handler = new WebhookHandler($this->secretKey);
        $handler->handle(['event' => 'PAYMENT RECEIVED', 'data' => []]);
    }
}
