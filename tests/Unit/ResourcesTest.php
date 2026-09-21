<?php

declare(strict_types=1);

namespace ClickPesa\Tests\Unit;

use ClickPesa\Auth\InMemoryTokenCache;
use ClickPesa\Auth\TokenManager;
use ClickPesa\ClickPesaClient;
use ClickPesa\Config;
use ClickPesa\Exceptions\ConflictException;
use ClickPesa\Exceptions\NotFoundException;
use ClickPesa\Exceptions\RateLimitException;
use ClickPesa\Exceptions\ValidationException;
use ClickPesa\Http\GuzzleHttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ResourcesTest extends TestCase
{
    private function createClientWithResponses(array $responses): ClickPesaClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handlerStack]);

        $config = new Config([
            'client_id' => 'test_client',
            'api_key' => 'test_key',
            'checksum_key' => 'test_secret',
            'checksum_enabled' => true,
        ]);

        $tokenCache = new InMemoryTokenCache();
        $tokenCache->set('clickpesa_auth_token_' . md5('test_client'), 'Bearer valid_mock_token', 3600);

        $tokenManager = new TokenManager($config, $tokenCache, $guzzle);
        $http = new GuzzleHttpClient($config, $tokenManager, $guzzle);

        return new ClickPesaClient($config, $tokenCache, $http);
    }

    public function test_ussd_push_preview_and_initiate(): void
    {
        $client = $this->createClientWithResponses([
            new Response(200, [], json_encode([
                'activeMethods' => [
                    ['name' => 'TIGO-PESA', 'status' => 'AVAILABLE', 'fee' => 100],
                ],
            ])),
            new Response(200, [], json_encode([
                'id' => 'PUSH123',
                'status' => 'PROCESSING',
                'channel' => 'TIGO-PESA',
                'orderReference' => 'ORD101',
                'collectedAmount' => '5000',
            ])),
        ]);

        $preview = $client->ussdPush()->preview([
            'amount' => '5000',
            'currency' => 'TZS',
            'orderReference' => 'ORD101',
            'phoneNumber' => '255712345678',
        ]);
        $this->assertSame('AVAILABLE', $preview['activeMethods'][0]['status']);

        $initiate = $client->ussdPush()->initiate([
            'amount' => '5000',
            'currency' => 'TZS',
            'orderReference' => 'ORD101',
            'phoneNumber' => '255712345678',
        ]);
        $this->assertSame('PROCESSING', $initiate['status']);
        $this->assertSame('PUSH123', $initiate['id']);
    }

    public function test_card_payment_preview_and_initiate(): void
    {
        $client = $this->createClientWithResponses([
            new Response(200, [], json_encode(['fee' => 200])),
            new Response(200, [], json_encode(['cardPaymentLink' => 'https://checkout.clickpesa.com/card/pay123'])),
        ]);

        $preview = $client->cardPayments()->preview([
            'amount' => '100',
            'currency' => 'USD',
            'orderReference' => 'CARD1',
        ]);
        $this->assertSame(200, $preview['fee']);

        $initiate = $client->cardPayments()->initiate([
            'amount' => '100',
            'currency' => 'USD',
            'orderReference' => 'CARD1',
            'customer' => ['id' => 'cust_1'],
        ]);
        $this->assertSame('https://checkout.clickpesa.com/card/pay123', $initiate['cardPaymentLink']);
    }

    public function test_checkout_link_generate(): void
    {
        $client = $this->createClientWithResponses([
            new Response(200, [], json_encode(['checkoutLink' => 'https://checkout.clickpesa.com/link123'])),
        ]);

        $res = $client->checkoutLinks()->generate([
            'totalPrice' => '10000',
            'orderReference' => 'ORDER99',
            'orderCurrency' => 'TZS',
        ]);
        $this->assertSame('https://checkout.clickpesa.com/link123', $res['checkoutLink']);
    }

    public function test_billpay_operations(): void
    {
        $client = $this->createClientWithResponses([
            new Response(200, [], json_encode(['billPayNumber' => '55042914871931'])),
            new Response(200, [], json_encode(['billPayNumber' => '55042914871932'])),
            new Response(200, [], json_encode(['billPayNumber' => '55042914871931', 'status' => 'ACTIVE'])),
            new Response(200, [], json_encode(['billPayNumber' => '55042914871931', 'status' => 'UPDATED'])),
        ]);

        $order = $client->billPay()->createOrder(['billAmount' => 5000]);
        $this->assertSame('55042914871931', $order['billPayNumber']);

        $customer = $client->billPay()->createCustomer();
        $this->assertSame('55042914871932', $customer['billPayNumber']);

        $details = $client->billPay()->get('55042914871931');
        $this->assertSame('ACTIVE', $details['status']);

        $updated = $client->billPay()->update('55042914871931', ['billAmount' => 6000]);
        $this->assertSame('UPDATED', $updated['status']);
    }

    public function test_payments_query(): void
    {
        $client = $this->createClientWithResponses([
            new Response(200, [], json_encode([
                ['id' => 'TX1', 'status' => 'SUCCESS', 'orderReference' => 'ORD1'],
            ])),
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 'TX1', 'status' => 'SUCCESS'],
                ],
                'totalCount' => 1,
            ])),
        ]);

        $payment = $client->payments()->get('ORD1');
        $this->assertSame('SUCCESS', $payment[0]['status']);

        $all = $client->payments()->all(['limit' => 10]);
        $this->assertSame(1, $all['totalCount']);
    }

    public function test_payout_operations_and_lipa_namba(): void
    {
        $client = $this->createClientWithResponses([
            new Response(200, [], json_encode([
                'id' => 'PAY1',
                'status' => 'SUCCESS',
                'amount' => '1047.10',
            ])),
            new Response(200, [], json_encode([
                ['name' => 'Vodacom M-Pesa', 'providerCode' => '503'],
            ])),
            new Response(200, [], json_encode([
                'id' => 'PAY2',
                'status' => 'AUTHORIZED',
            ])),
        ]);

        $mno = $client->mobileMoneyPayouts()->create([
            'amount' => 1000,
            'phoneNumber' => '255712345678',
            'currency' => 'TZS',
            'orderReference' => 'PO1',
        ]);
        $this->assertSame('SUCCESS', $mno['status']);

        $providers = $client->lipaNambaPayouts()->providers();
        $this->assertSame('503', $providers[0]['providerCode']);

        $lipa = $client->lipaNambaPayouts()->create([
            'amount' => 1000,
            'currency' => 'TZS',
            'orderReference' => 'PO2',
            'lipaNamba' => '48001268',
            'providerCode' => '503',
        ]);
        $this->assertSame('AUTHORIZED', $lipa['status']);
    }

    public function test_balance_and_statement(): void
    {
        $client = $this->createClientWithResponses([
            new Response(200, [], json_encode([
                ['currency' => 'TZS', 'balance' => 500000],
            ])),
            new Response(404, [], json_encode(['message' => 'No balance account is linked to this merchant yet.'])),
            new Response(200, [], json_encode([
                ['name' => 'CRDB Bank', 'bic' => 'CORUTZTZ'],
            ])),
            new Response(200, [], json_encode([
                ['source' => 'USD', 'target' => 'TZS', 'rate' => 2500],
            ])),
        ]);

        $balance = $client->balance()->get();
        $this->assertSame(500000, $balance[0]['balance']);

        // Test 404 suppressed into empty array
        $emptyBalance = $client->balance()->get(suppressNotFound: true);
        $this->assertSame([], $emptyBalance);

        $banks = $client->banks()->all();
        $this->assertSame('CORUTZTZ', $banks[0]['bic']);

        $rates = $client->exchangeRates()->all();
        $this->assertSame(2500, $rates[0]['rate']);
    }

    public function test_rate_limit_exception_on_payout_cooldown(): void
    {
        $client = $this->createClientWithResponses([
            new Response(400, [], json_encode([
                'message' => 'Payout request is already in progress, please retry after 42 seconds',
            ])),
        ]);

        try {
            $client->mobileMoneyPayouts()->create([
                'amount' => 1000,
                'phoneNumber' => '255712345678',
                'currency' => 'TZS',
                'orderReference' => 'PO_RL',
            ]);
            $this->fail('Expected RateLimitException was not thrown.');
        } catch (RateLimitException $e) {
            $this->assertSame(42, $e->getRetryAfterSeconds());
            $this->assertSame(400, $e->getStatusCode());
        }
    }

    public function test_conflict_exception_on_duplicate_reference(): void
    {
        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage('Order reference already used');

        $client = $this->createClientWithResponses([
            new Response(409, [], json_encode([
                'message' => 'Order reference already used: Create a different reference',
            ])),
        ]);

        $client->ussdPush()->initiate([
            'amount' => '1000',
            'currency' => 'TZS',
            'orderReference' => 'DUPLICATE',
            'phoneNumber' => '255700000000',
        ]);
    }
}
