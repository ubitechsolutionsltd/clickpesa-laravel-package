<?php

declare(strict_types=1);

namespace ClickPesa\Http;

interface HttpClientInterface
{
    /**
     * Send an HTTP request to the ClickPesa API.
     *
     * @param string $method GET, POST, PATCH, DELETE, etc.
     * @param string $endpoint e.g., '/payments/initiate-ussd-push-request'
     * @param array<string, mixed> $options Guzzle options (json, headers, query, etc.)
     * @param bool $authenticated Whether to attach the Bearer authorization header
     * @return array<string, mixed> Decoded JSON response
     *
     * @throws \ClickPesa\Exceptions\ClickPesaException
     */
    public function request(string $method, string $endpoint, array $options = [], bool $authenticated = true): array;
}
