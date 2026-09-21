<?php

declare(strict_types=1);

namespace ClickPesa\Auth;

interface TokenCacheInterface
{
    /**
     * Get a cached token by key.
     */
    public function get(string $key): ?string;

    /**
     * Store a token in cache with a TTL (in seconds).
     */
    public function set(string $key, string $token, int $ttlSeconds): void;

    /**
     * Check if cache has a valid token for key.
     */
    public function has(string $key): bool;

    /**
     * Remove a cached token.
     */
    public function forget(string $key): void;
}
