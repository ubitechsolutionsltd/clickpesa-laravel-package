<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Http\Controllers;

use ClickPesa\Exceptions\WebhookVerificationException;
use ClickPesa\Laravel\Events\DepositReceived;
use ClickPesa\Laravel\Events\PaymentFailed;
use ClickPesa\Laravel\Events\PaymentReceived;
use ClickPesa\Laravel\Events\PayoutInitiated;
use ClickPesa\Laravel\Events\PayoutRefunded;
use ClickPesa\Laravel\Events\PayoutReversed;
use ClickPesa\Webhooks\Events\DepositReceivedEvent;
use ClickPesa\Webhooks\Events\PaymentFailedEvent;
use ClickPesa\Webhooks\Events\PaymentReceivedEvent;
use ClickPesa\Webhooks\Events\PayoutInitiatedEvent;
use ClickPesa\Webhooks\Events\PayoutRefundedEvent;
use ClickPesa\Webhooks\Events\PayoutReversedEvent;
use ClickPesa\Webhooks\WebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ClickPesaWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $checksumKey = config('clickpesa.checksum_key');
        $handler = new WebhookHandler($checksumKey);

        $payload = $request->all();
        $receivedChecksum = $request->header('x-clickpesa-checksum')
            ?? $request->header('checksum')
            ?? ($payload['checksum'] ?? null);

        try {
            $event = $handler->handle($payload, $receivedChecksum);
        } catch (WebhookVerificationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid webhook payload: ' . $e->getMessage(),
            ], 400);
        }

        // Dispatch native Laravel events
        $this->dispatchEvent($event);

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
        ], 200);
    }

    private function dispatchEvent(mixed $event): void
    {
        match (true) {
            $event instanceof PaymentReceivedEvent => event(new PaymentReceived($event)),
            $event instanceof PaymentFailedEvent => event(new PaymentFailed($event)),
            $event instanceof PayoutInitiatedEvent => event(new PayoutInitiated($event)),
            $event instanceof PayoutRefundedEvent => event(new PayoutRefunded($event)),
            $event instanceof PayoutReversedEvent => event(new PayoutReversed($event)),
            $event instanceof DepositReceivedEvent => event(new DepositReceived($event)),
            default => null,
        };
    }
}
