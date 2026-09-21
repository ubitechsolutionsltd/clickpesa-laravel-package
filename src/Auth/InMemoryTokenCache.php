<?php

declare(strict_types=1);

namespace ClickPesa\Auth;

class InMemoryTokenCache implements TokenCacheInterface
{
    /**
     * @var array<string, array{token: string, expires_at: int}>
     */
    private array $storage = [];

    public function get(string $key): ?string
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->storage[$key]['token'];
    }

    public function set(string $key, string $token, int $ttlSeconds): void
    {
        $this->storage[$key] = [
            'token' => $token,
            'expires_at' => time() + $ttlSeconds,
        ];
    }

    public function has(string $key): bool
    {
        if (!isset($this->storage[$key])) {
            return false;
        }

        if (time() >= $this->storage[$key]['expires_at']) {
            unset($this->storage[$key]);
            return false;
        }

        return true;
    }

    public function forget(string $key): void
    {
        unset($this->storage[$key]);
    }
}
