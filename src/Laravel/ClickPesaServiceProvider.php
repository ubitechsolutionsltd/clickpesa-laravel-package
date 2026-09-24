<?php

declare(strict_types=1);

namespace ClickPesa\Laravel;

use ClickPesa\Auth\LaravelTokenCache;
use ClickPesa\Auth\TokenCacheInterface;
use ClickPesa\ClickPesaClient;
use ClickPesa\Config;
use ClickPesa\Laravel\Http\Controllers\ClickPesaWebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ClickPesaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/clickpesa.php', 'clickpesa');

        $this->app->singleton(Config::class, function ($app) {
            $config = $app['config']->get('clickpesa', []);

            return new Config([
                'client_id' => $config['client_id'] ?? '',
                'api_key' => $config['api_key'] ?? '',
                'checksum_key' => $config['checksum_key'] ?? null,
                'checksum_enabled' => (bool) ($config['checksum_enabled'] ?? false),
                'base_url' => $config['base_url'] ?? Config::DEFAULT_BASE_URL,
                'token_ttl_margin' => (int) ($config['token_ttl_margin'] ?? 300),
                'timeout' => (int) ($config['timeout'] ?? 30),
                'pricing' => $config['pricing'] ?? [],
            ]);
        });

        $this->app->bind(TokenCacheInterface::class, function ($app) {
            return new LaravelTokenCache($app['cache.store']);
        });

        $this->app->singleton(ClickPesaClient::class, function ($app) {
            $config = $app->make(Config::class);
            $tokenCache = $app->make(TokenCacheInterface::class);

            return new ClickPesaClient($config, $tokenCache);
        });

        $this->app->alias(ClickPesaClient::class, 'clickpesa');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/clickpesa.php' => $this->app->configPath('clickpesa.php'),
            ], 'clickpesa-config');
        }

        // Register route macro for webhooks
        Route::macro('clickpesaWebhooks', function (string $url = 'clickpesa/webhooks') {
            return Route::post($url, ClickPesaWebhookController::class)
                ->name('clickpesa.webhooks');
        });
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Config::class,
            TokenCacheInterface::class,
            ClickPesaClient::class,
            'clickpesa',
        ];
    }
}
