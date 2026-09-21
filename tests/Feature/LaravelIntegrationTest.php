<?php

declare(strict_types=1);

namespace ClickPesa\Tests\Feature;

use ClickPesa\ClickPesaClient;
use ClickPesa\Laravel\Events\PaymentReceived;
use ClickPesa\Laravel\Facades\ClickPesa;
use ClickPesa\Security\Checksum;
use ClickPesa\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

class LaravelIntegrationTest extends TestCase
{
    public function test_service_provider_registers_client(): void
    {
        $this->assertTrue($this->app->bound(ClickPesaClient::class));
        $this->assertTrue($this->app->bound('clickpesa'));

        $client = $this->app->make(ClickPesaClient::class);
        $this->assertInstanceOf(ClickPesaClient::class, $client);
        $this->assertSame('test_client_id', $client->getConfig()->getClientId());
    }

    public function test_facade_fake_and_assertions(): void
    {
        ClickPesa::fake();

        $response = ClickPesa::ussdPush()->initiate([
            'amount' => '5000',
            'currency' => 'TZS',
            'orderReference' => 'INV_1001',
            'phoneNumber' => '255712345678',
        ]);

        $this->assertSame('SUCCESS', $response['status']);

        ClickPesa::assertInitiatedUssdPush(function ($data) {
            return $data['orderReference'] === 'INV_1001' && $data['amount'] === '5000';
        });

        ClickPesa::assertNotSent('/payouts/create-bank-payout');
    }

    public function test_webhook_route_macro_and_event_dispatching(): void
    {
        Event::fake([PaymentReceived::class]);

        Route::clickpesaWebhooks('webhooks/clickpesa');

        $checksumKey = 'test_checksum_key';
        $payload = [
            'event' => 'PAYMENT RECEIVED',
            'data' => [
                'id' => 'TX_WEBHOOK_1',
                'status' => 'SUCCESS',
                'orderReference' => 'ORD_TEST_99',
                'collectedAmount' => '25000',
                'collectedCurrency' => 'TZS',
                'paymentReference' => 'PAY_REF_123',
            ],
        ];

        $checksum = Checksum::generate($checksumKey, $payload);
        $payload['checksum'] = $checksum;

        $response = $this->postJson('webhooks/clickpesa', $payload, [
            'x-clickpesa-checksum' => $checksum,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        Event::assertDispatched(PaymentReceived::class, function (PaymentReceived $event) {
            return $event->getOrderReference() === 'ORD_TEST_99'
                && $event->getCollectedAmount() === '25000'
                && $event->getPaymentReference() === 'PAY_REF_123';
        });
    }

    public function test_webhook_rejects_invalid_checksum(): void
    {
        Event::fake([PaymentReceived::class]);

        Route::clickpesaWebhooks('webhooks/clickpesa');

        $payload = [
            'event' => 'PAYMENT RECEIVED',
            'data' => [
                'orderReference' => 'ORD_TAMPERED',
            ],
            'checksum' => 'invalid_checksum_value',
        ];

        $response = $this->postJson('webhooks/clickpesa', $payload);

        $response->assertStatus(400);
        $response->assertJson(['status' => 'error']);

        Event::assertNotDispatched(PaymentReceived::class);
    }
}
