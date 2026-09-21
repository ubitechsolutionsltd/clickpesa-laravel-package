<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Testing;

use ClickPesa\Auth\InMemoryTokenCache;
use ClickPesa\ClickPesaClient;
use ClickPesa\Config;
use ClickPesa\Http\HttpClientInterface;
use PHPUnit\Framework\Assert as PHPUnit;

class ClickPesaFake extends ClickPesaClient
{
    /**
     * @var array<int, array{method: string, endpoint: string, options: array<string, mixed>}>
     */
    private array $recordedRequests = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $responses = [];

    public function __construct(array $responses = [])
    {
        $this->responses = $responses;

        $config = new Config([
            'client_id' => 'fake_client_id',
            'api_key' => 'fake_api_key',
            'checksum_key' => 'fake_checksum_key',
            'checksum_enabled' => false,
        ]);

        $mockHttp = new class($this) implements HttpClientInterface {
            private ClickPesaFake $fake;

            public function __construct(ClickPesaFake $fake)
            {
                $this->fake = $fake;
            }

            public function request(string $method, string $endpoint, array $options = [], bool $authenticated = true): array
            {
                return $this->fake->recordAndRespond($method, $endpoint, $options);
            }
        };

        parent::__construct($config, new InMemoryTokenCache(), $mockHttp);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function recordAndRespond(string $method, string $endpoint, array $options = []): array
    {
        $this->recordedRequests[] = [
            'method' => $method,
            'endpoint' => $endpoint,
            'options' => $options,
        ];

        $key = strtoupper($method) . ' ' . ltrim($endpoint, '/');

        return $this->responses[$key]
            ?? $this->responses[$endpoint]
            ?? [
                'status' => 'SUCCESS',
                'success' => true,
                'id' => 'fake_id_' . uniqid(),
                'orderReference' => $options['json']['orderReference'] ?? 'fake_ref',
            ];
    }

    /**
     * Assert that a request matching criteria was made.
     *
     * @param string $endpoint
     * @param callable|null $callback fn(array $options, string $method): bool
     */
    public function assertSent(string $endpoint, ?callable $callback = null): void
    {
        $matching = array_filter($this->recordedRequests, function ($req) use ($endpoint, $callback) {
            $endpointMatches = str_ends_with($req['endpoint'], ltrim($endpoint, '/'));
            if (!$endpointMatches) {
                return false;
            }

            return $callback ? $callback($req['options'], $req['method']) : true;
        });

        PHPUnit::assertTrue(
            count($matching) > 0,
            "Failed asserting that a request to [{$endpoint}] was sent."
        );
    }

    /**
     * Assert that no requests matching the endpoint were sent.
     */
    public function assertNotSent(string $endpoint): void
    {
        $matching = array_filter($this->recordedRequests, function ($req) use ($endpoint) {
            return str_ends_with($req['endpoint'], ltrim($endpoint, '/'));
        });

        PHPUnit::assertCount(
            0,
            $matching,
            "Failed asserting that no request to [{$endpoint}] was sent."
        );
    }

    /**
     * Assert that USSD Push was initiated.
     *
     * @param callable|null $callback fn(array $data): bool
     */
    public function assertInitiatedUssdPush(?callable $callback = null): void
    {
        $this->assertSent('/payments/initiate-ussd-push-request', function ($options) use ($callback) {
            $data = $options['json'] ?? [];
            return $callback ? $callback($data) : true;
        });
    }

    /**
     * Assert that Checkout Link was generated.
     *
     * @param callable|null $callback fn(array $data): bool
     */
    public function assertGeneratedCheckoutLink(?callable $callback = null): void
    {
        $this->assertSent('/checkout-link/generate-checkout-url', function ($options) use ($callback) {
            $data = $options['json'] ?? [];
            return $callback ? $callback($data) : true;
        });
    }

    /**
     * Assert that a Payout was created.
     *
     * @param string $type mno|bank|lipa-namba
     * @param callable|null $callback
     */
    public function assertCreatedPayout(string $type = 'mno', ?callable $callback = null): void
    {
        $endpoint = match ($type) {
            'bank' => '/payouts/create-bank-payout',
            'lipa-namba' => '/payouts/create-lipa-namba-payout',
            default => '/payouts/create-mobile-money-payout',
        };

        $this->assertSent($endpoint, function ($options) use ($callback) {
            $data = $options['json'] ?? [];
            return $callback ? $callback($data) : true;
        });
    }

    /**
     * Assert no requests were sent at all.
     */
    public function assertNothingSent(): void
    {
        PHPUnit::assertEmpty($this->recordedRequests, 'Expected no requests to have been sent.');
    }

    /**
     * @return array<int, array{method: string, endpoint: string, options: array<string, mixed>}>
     */
    public function recorded(): array
    {
        return $this->recordedRequests;
    }
}
