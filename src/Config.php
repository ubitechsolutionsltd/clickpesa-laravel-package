<?php

declare(strict_types=1);

namespace ClickPesa;

use InvalidArgumentException;

class Config
{
    public const DEFAULT_BASE_URL = 'https://api.clickpesa.com/third-parties';
    public const DEFAULT_TOKEN_BUFFER_SECONDS = 300; // 5 minutes buffer before 1 hour expiry

    private string $clientId;
    private string $apiKey;
    private ?string $checksumKey;
    private bool $checksumEnabled;
    private string $baseUrl;
    private int $tokenTtlMargin;
    private int $timeout;
    /** @var array<string, mixed> */
    private array $pricing;

    /**
     * @param array{
     *     client_id: string,
     *     api_key: string,
     *     checksum_key?: string|null,
     *     checksum_enabled?: bool,
     *     base_url?: string,
     *     token_ttl_margin?: int,
     *     timeout?: int,
     *     pricing?: array<string, mixed>
     * } $options
     */
    public function __construct(array $options)
    {
        if (empty($options['client_id'])) {
            throw new InvalidArgumentException('ClickPesa client_id is required.');
        }

        if (empty($options['api_key'])) {
            throw new InvalidArgumentException('ClickPesa api_key is required.');
        }

        $this->clientId = (string) $options['client_id'];
        $this->apiKey = (string) $options['api_key'];
        $this->checksumKey = isset($options['checksum_key']) && !empty($options['checksum_key'])
            ? (string) $options['checksum_key']
            : null;
        $this->checksumEnabled = (bool) ($options['checksum_enabled'] ?? !empty($this->checksumKey));
        $this->baseUrl = rtrim((string) ($options['base_url'] ?? self::DEFAULT_BASE_URL), '/');
        $this->tokenTtlMargin = (int) ($options['token_ttl_margin'] ?? self::DEFAULT_TOKEN_BUFFER_SECONDS);
        $this->timeout = (int) ($options['timeout'] ?? 30);
        $this->pricing = (array) ($options['pricing'] ?? []);
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getChecksumKey(): ?string
    {
        return $this->checksumKey;
    }

    public function isChecksumEnabled(): bool
    {
        return $this->checksumEnabled;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTokenTtlMargin(): int
    {
        return $this->tokenTtlMargin;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPricing(): array
    {
        return $this->pricing;
    }

    public function getPricingOption(string $key, mixed $default = null): mixed
    {
        return $this->pricing[$key] ?? $default;
    }
}
