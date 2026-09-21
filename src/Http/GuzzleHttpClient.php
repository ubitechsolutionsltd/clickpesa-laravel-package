<?php

declare(strict_types=1);

namespace ClickPesa\Http;

use ClickPesa\Auth\TokenManager;
use ClickPesa\Config;
use ClickPesa\Exceptions\AuthenticationException;
use ClickPesa\Exceptions\ClickPesaException;
use ClickPesa\Exceptions\ConflictException;
use ClickPesa\Exceptions\NotFoundException;
use ClickPesa\Exceptions\RateLimitException;
use ClickPesa\Exceptions\ValidationException;
use ClickPesa\Security\Checksum;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private Config $config;
    private TokenManager $tokenManager;
    private ClientInterface $client;

    public function __construct(
        Config $config,
        TokenManager $tokenManager,
        ?ClientInterface $client = null
    ) {
        $this->config = $config;
        $this->tokenManager = $tokenManager;
        $this->client = $client ?? new GuzzleClient([
            'base_uri' => $this->config->getBaseUrl(),
            'timeout' => $this->config->getTimeout(),
        ]);
    }

    /**
     * Send HTTP request to ClickPesa API.
     *
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     * @param bool $authenticated
     * @return array<string, mixed>
     * @throws ClickPesaException
     */
    public function request(string $method, string $endpoint, array $options = [], bool $authenticated = true): array
    {
        return $this->executeRequest($method, $endpoint, $options, $authenticated, retryOnAuthFailure: true);
    }

    /**
     * @throws ClickPesaException
     */
    private function executeRequest(
        string $method,
        string $endpoint,
        array $options,
        bool $authenticated,
        bool $retryOnAuthFailure
    ): array {
        $headers = $options['headers'] ?? [];
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';

        if ($authenticated) {
            $headers['Authorization'] = $this->tokenManager->getAccessToken();
        }

        // Automatic Checksum signing for JSON payloads if checksum is enabled
        if (isset($options['json']) && is_array($options['json']) && $this->config->isChecksumEnabled() && $this->config->getChecksumKey()) {
            if (!isset($options['json']['checksum'])) {
                $options['json']['checksum'] = Checksum::generate(
                    $this->config->getChecksumKey(),
                    $options['json']
                );
            }
        }

        $options['headers'] = $headers;
        $uri = ltrim($endpoint, '/');

        try {
            $response = $this->client->request($method, $uri, $options);
            return $this->parseResponse($response);
        } catch (BadResponseException $e) {
            // Handle automatic token refresh once on 401
            if ($authenticated && $retryOnAuthFailure && $e->getResponse()->getStatusCode() === 401) {
                $this->tokenManager->refreshToken();
                return $this->executeRequest($method, $endpoint, $options, $authenticated, retryOnAuthFailure: false);
            }

            $this->handleBadResponseException($e);
        } catch (GuzzleException $e) {
            throw new ClickPesaException(
                'ClickPesa request error: ' . $e->getMessage(),
                (int) $e->getCode(),
                null,
                null,
                $e
            );
        }

        return [];
    }

    private function parseResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '' || $body === '0') {
            return [];
        }

        $data = json_decode($body, true);

        return is_array($data) ? $data : ['data' => $data];
    }

    /**
     * @throws ClickPesaException
     */
    private function handleBadResponseException(BadResponseException $e): void
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        $data = is_array($json) ? $json : [];

        $message = $data['message'] ?? $body;
        if (!is_string($message) || $message === '') {
            $message = 'HTTP error ' . $statusCode;
        }

        switch ($statusCode) {
            case 400:
                // Check if this is a payout rate limit error
                if (preg_match('/retry after (\d+) seconds/i', $message, $matches)) {
                    $retryAfter = (int) $matches[1];
                    throw new RateLimitException($message, $statusCode, $statusCode, $data, $retryAfter);
                }
                throw new ValidationException($message, $statusCode, $statusCode, $data, $e);

            case 401:
            case 403:
                throw new AuthenticationException($message, $statusCode, $statusCode, $data, $e);

            case 404:
                throw new NotFoundException($message, $statusCode, $statusCode, $data, $e);

            case 409:
                throw new ConflictException($message, $statusCode, $statusCode, $data, $e);

            default:
                throw new ClickPesaException($message, $statusCode, $statusCode, $data, $e);
        }
    }
}
