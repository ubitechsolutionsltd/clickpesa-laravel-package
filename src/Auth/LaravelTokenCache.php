<?php

declare(strict_types=1);

namespace ClickPesa\Auth;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class LaravelTokenCache implements TokenCacheInterface
{
    private CacheRepository $cache;

    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $key): ?string
    {
        $value = $this->cache->get($key);

        return is_string($value) ? $value : null;
    }

    public function set(string $key, string $token, int $ttlSeconds): void
    {
        $this->cache->put($key, $token, $ttlSeconds);
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }
}
