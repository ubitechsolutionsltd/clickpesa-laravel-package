<?php

declare(strict_types=1);

namespace ClickPesa\Auth;

use ClickPesa\Config;
use ClickPesa\Exceptions\AuthenticationException;
use ClickPesa\Exceptions\ClickPesaException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class TokenManager
{
    private Config $config;
    private TokenCacheInterface $cache;
    private ClientInterface $client;

    public function __construct(
        Config $config,
        ?TokenCacheInterface $cache = null,
        ?ClientInterface $client = null
    ) {
        $this->config = $config;
        $this->cache = $cache ?? new InMemoryTokenCache();
        $this->client = $client ?? new GuzzleClient([
            'base_uri' => $this->config->getBaseUrl(),
            'timeout' => $this->config->getTimeout(),
        ]);
    }

    /**
     * Get a valid access token. Retrieves from cache if valid, otherwise requests a new one.
     *
     * @return string Token ready for Authorization header (e.g. "Bearer eyJ...")
     * @throws AuthenticationException|ClickPesaException
     */
    public function getAccessToken(): string
    {
        $cacheKey = $this->getCacheKey();

        $token = $this->cache->get($cacheKey);
        if ($token !== null && $token !== '') {
            return $this->formatBearerToken($token);
        }

        return $this->refreshToken();
    }

    /**
     * Force a new token generation and store it in cache.
     *
     * @return string
     * @throws AuthenticationException|ClickPesaException
     */
    public function refreshToken(): string
    {
        $cacheKey = $this->getCacheKey();
        $this->cache->forget($cacheKey);

        try {
            $response = $this->client->request('POST', $this->config->getBaseUrl() . '/generate-token', [
                'headers' => [
                    'Accept' => 'application/json',
                    'client-id' => $this->config->getClientId(),
                    'api-key' => $this->config->getApiKey(),
                ],
            ]);

            $token = $this->parseTokenFromResponse($response);

            // JWT tokens are valid for 1 hour (3600 seconds)
            $ttl = max(60, 3600 - $this->config->getTokenTtlMargin());
            $this->cache->set($cacheKey, $token, $ttl);

            return $this->formatBearerToken($token);
        } catch (GuzzleException $e) {
            $this->handleGuzzleException($e);
            throw new ClickPesaException('Failed to generate token: ' . $e->getMessage(), (int) $e->getCode(), null, null, $e);
        }
    }

    public function clearToken(): void
    {
        $this->cache->forget($this->getCacheKey());
    }

    public function getCache(): TokenCacheInterface
    {
        return $this->cache;
    }

    private function getCacheKey(): string
    {
        return 'clickpesa_auth_token_' . md5($this->config->getClientId());
    }

    private function formatBearerToken(string $token): string
    {
        return str_starts_with($token, 'Bearer ') ? $token : 'Bearer ' . $token;
    }

    /**
     * @throws AuthenticationException
     */
    private function parseTokenFromResponse(ResponseInterface $response): string
    {
        $contents = (string) $response->getBody();
        $data = json_decode($contents, true);

        if (!is_array($data) || empty($data['token'])) {
            throw new AuthenticationException(
                'Invalid token response from ClickPesa API: ' . $contents,
                $response->getStatusCode(),
                $response->getStatusCode(),
                is_array($data) ? $data : null
            );
        }

        return (string) $data['token'];
    }

    /**
     * @throws AuthenticationException
     */
    private function handleGuzzleException(GuzzleException $e): void
    {
        if (method_exists($e, 'getResponse') && $e->getResponse() instanceof ResponseInterface) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $json = json_decode($body, true);

            $message = is_array($json) && isset($json['message'])
                ? (string) $json['message']
                : 'Authentication failed with status ' . $statusCode;

            if ($statusCode === 401 || $statusCode === 403) {
                throw new AuthenticationException($message, $statusCode, $statusCode, is_array($json) ? $json : null, $e);
            }
        }
    }
}
