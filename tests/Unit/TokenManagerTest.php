<?php

declare(strict_types=1);

namespace ClickPesa\Tests\Unit;

use ClickPesa\Auth\InMemoryTokenCache;
use ClickPesa\Auth\TokenManager;
use ClickPesa\Config;
use ClickPesa\Exceptions\AuthenticationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class TokenManagerTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Config([
            'client_id' => 'test_client_id',
            'api_key' => 'test_api_key',
        ]);
    }

    public function test_it_fetches_and_caches_token(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['success' => true, 'token' => 'Bearer sample_jwt_123'])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cache = new InMemoryTokenCache();
        $tokenManager = new TokenManager($this->config, $cache, $client);

        $token = $tokenManager->getAccessToken();
        $this->assertSame('Bearer sample_jwt_123', $token);

        // Second call should return from cache without requesting Guzzle again (no more responses in MockHandler)
        $cachedToken = $tokenManager->getAccessToken();
        $this->assertSame('Bearer sample_jwt_123', $cachedToken);
    }

    public function test_it_adds_bearer_prefix_if_missing_in_response(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['success' => true, 'token' => 'sample_jwt_without_bearer'])),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $tokenManager = new TokenManager($this->config, new InMemoryTokenCache(), $client);
        $token = $tokenManager->getAccessToken();

        $this->assertSame('Bearer sample_jwt_without_bearer', $token);
    }

    public function test_it_throws_authentication_exception_on_401_or_403(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid or Expired API-Key');

        $mock = new MockHandler([
            new Response(403, [], json_encode(['message' => 'Invalid or Expired API-Key'])),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $tokenManager = new TokenManager($this->config, new InMemoryTokenCache(), $client);
        $tokenManager->getAccessToken();
    }

    public function test_it_refreshes_token_when_requested(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['success' => true, 'token' => 'Bearer token_1'])),
            new Response(200, [], json_encode(['success' => true, 'token' => 'Bearer token_2'])),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $cache = new InMemoryTokenCache();
        $tokenManager = new TokenManager($this->config, $cache, $client);

        $this->assertSame('Bearer token_1', $tokenManager->getAccessToken());
        $this->assertSame('Bearer token_2', $tokenManager->refreshToken());
        $this->assertSame('Bearer token_2', $tokenManager->getAccessToken());
    }
}
