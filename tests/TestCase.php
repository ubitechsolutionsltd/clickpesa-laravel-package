<?php

declare(strict_types=1);

namespace ClickPesa\Tests;

use ClickPesa\Laravel\ClickPesaServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ClickPesaServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'ClickPesa' => \ClickPesa\Laravel\Facades\ClickPesa::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('clickpesa.client_id', 'test_client_id');
        $app['config']->set('clickpesa.api_key', 'test_api_key');
        $app['config']->set('clickpesa.checksum_key', 'test_checksum_key');
        $app['config']->set('clickpesa.checksum_enabled', true);
    }
}
