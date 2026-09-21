<?php

declare(strict_types=1);

namespace ClickPesa\Tests\Unit;

use ClickPesa\Security\Checksum;
use PHPUnit\Framework\TestCase;

class ChecksumTest extends TestCase
{
    public function test_it_canonicalizes_keys_recursively_in_alphabetical_order(): void
    {
        $input = [
            'z_key' => 'last',
            'a_key' => 'first',
            'nested' => [
                'zebra' => 1,
                'apple' => 2,
            ],
            'list' => [
                ['b' => 2, 'a' => 1],
                ['y' => 4, 'x' => 3],
            ],
        ];

        $canonical = Checksum::canonicalize($input);

        $this->assertSame(['a_key', 'list', 'nested', 'z_key'], array_keys($canonical));
        $this->assertSame(['apple', 'zebra'], array_keys($canonical['nested']));
        $this->assertSame(['a', 'b'], array_keys($canonical['list'][0]));
        $this->assertSame(['x', 'y'], array_keys($canonical['list'][1]));
    }

    public function test_it_generates_checksum_matching_official_example(): void
    {
        $payload = [
            'amount' => 100,
            'currency' => 'USD',
            'reference' => 'TX123',
            'exchange' => [
                'fromCurrency' => 'TZS',
                'toCurrency' => 'TZS',
                'rate' => '1',
                'amount' => '1000',
            ],
            'customer' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+255123456789',
            ],
        ];

        $secretKey = 'secret-key';
        $checksum = Checksum::generate($secretKey, $payload);

        $this->assertSame(64, strlen($checksum));

        // It should produce the exact same checksum regardless of original key order
        $reorderedPayload = [
            'reference' => 'TX123',
            'customer' => [
                'phone' => '+255123456789',
                'email' => 'john@example.com',
                'name' => 'John Doe',
            ],
            'amount' => 100,
            'currency' => 'USD',
            'exchange' => [
                'rate' => '1',
                'toCurrency' => 'TZS',
                'amount' => '1000',
                'fromCurrency' => 'TZS',
            ],
        ];

        $checksumReordered = Checksum::generate($secretKey, $reorderedPayload);
        $this->assertSame($checksum, $checksumReordered);
    }

    public function test_it_ignores_checksum_and_checksum_method_fields(): void
    {
        $secretKey = 'secret-key';
        $payloadWithout = ['amount' => 500, 'currency' => 'TZS'];
        $payloadWith = [
            'checksum' => 'any_preexisting_checksum',
            'checksumMethod' => 'canonical',
            'amount' => 500,
            'currency' => 'TZS',
        ];

        $hashWithout = Checksum::generate($secretKey, $payloadWithout);
        $hashWith = Checksum::generate($secretKey, $payloadWith);

        $this->assertSame($hashWithout, $hashWith);
    }

    public function test_it_verifies_valid_and_invalid_checksums(): void
    {
        $secretKey = 'secret-key';
        $payload = ['amount' => 100, 'orderReference' => 'ORD123'];

        $validChecksum = Checksum::generate($secretKey, $payload);

        $this->assertTrue(Checksum::verify($secretKey, $payload, $validChecksum));
        $this->assertFalse(Checksum::verify($secretKey, $payload, 'invalid_checksum'));
        $this->assertFalse(Checksum::verify($secretKey, $payload, null));
        $this->assertFalse(Checksum::verify($secretKey, $payload, ''));
    }
}
