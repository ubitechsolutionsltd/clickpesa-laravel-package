<?php

declare(strict_types=1);

namespace ClickPesa\Security;

class Checksum
{
    /**
     * Recursively canonicalize payload by sorting object keys alphabetically.
     * Sequential numeric arrays (lists) maintain their element ordering while
     * nested elements are recursively canonicalized.
     *
     * @param mixed $obj
     * @return mixed
     */
    public static function canonicalize(mixed $obj): mixed
    {
        if ($obj === null || !is_array($obj)) {
            return $obj;
        }

        // Check if array is a sequential list (keys 0, 1, 2, ...)
        if (array_values($obj) === $obj) {
            return array_map([self::class, 'canonicalize'], $obj);
        }

        ksort($obj);
        $result = [];
        foreach ($obj as $key => $value) {
            $result[$key] = self::canonicalize($value);
        }

        return $result;
    }

    /**
     * Generate HMAC-SHA256 checksum for payload using ClickPesa specifications.
     * Excludes `checksum` and `checksumMethod` fields before hashing.
     *
     * @param string $checksumKey
     * @param array<string, mixed> $payload
     * @return string 64-character hexadecimal string
     */
    public static function generate(string $checksumKey, array $payload): string
    {
        // Exclude checksum and checksumMethod
        unset($payload['checksum'], $payload['checksumMethod']);

        $canonicalPayload = self::canonicalize($payload);
        $payloadString = json_encode($canonicalPayload, JSON_UNESCAPED_SLASHES);

        return hash_hmac('sha256', (string) $payloadString, $checksumKey);
    }

    /**
     * Verify received checksum against payload using timing-safe comparison.
     *
     * @param string $checksumKey
     * @param array<string, mixed> $payload
     * @param string|null $receivedChecksum
     * @return bool
     */
    public static function verify(string $checksumKey, array $payload, ?string $receivedChecksum): bool
    {
        if (empty($receivedChecksum)) {
            return false;
        }

        $computedChecksum = self::generate($checksumKey, $payload);

        return hash_equals($computedChecksum, $receivedChecksum);
    }
}
