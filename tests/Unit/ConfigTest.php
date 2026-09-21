<?php

declare(strict_types=1);

namespace ClickPesa\Tests\Unit;

use ClickPesa\Config;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function test_it_initializes_with_valid_options(): void
    {
        $config = new Config([
            'client_id' => 'my_client',
            'api_key' => 'my_key',
            'checksum_key' => 'my_checksum_secret',
            'checksum_enabled' => true,
            'base_url' => 'https://api.clickpesa.com/third-parties/',
            'token_ttl_margin' => 400,
            'timeout' => 45,
        ]);

        $this->assertSame('my_client', $config->getClientId());
        $this->assertSame('my_key', $config->getApiKey());
        $this->assertSame('my_checksum_secret', $config->getChecksumKey());
        $this->assertTrue($config->isChecksumEnabled());
        $this->assertSame('https://api.clickpesa.com/third-parties', $config->getBaseUrl());
        $this->assertSame(400, $config->getTokenTtlMargin());
        $this->assertSame(45, $config->getTimeout());
    }

    public function test_it_throws_if_client_id_is_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ClickPesa client_id is required.');

        new Config([
            'client_id' => '',
            'api_key' => 'my_key',
        ]);
    }

    public function test_it_throws_if_api_key_is_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ClickPesa api_key is required.');

        new Config([
            'client_id' => 'my_client',
            'api_key' => '',
        ]);
    }
}
