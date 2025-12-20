<?php

namespace App\Providers;

use App\Services\Lightspeed\LightspeedOrderSyncService;
use App\Services\Lightspeed\LightspeedSyncService;
use App\Services\Lightspeed\ProductMappingCache;
use App\Services\Lightspeed\Sync\StockSyncStrategy;
use Illuminate\Support\ServiceProvider;

class LightspeedServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ProductMappingCache::class, function ($app) {
            return new ProductMappingCache(
                ttl: config('lightspeed.cache.ttl', 3600),
                keyPrefix: config('lightspeed.cache.key_prefix', 'lightspeed')
            );
        });

        $this->app->singleton(LightspeedSyncService::class, function ($app) {
            $service = new LightspeedSyncService(
                cache: $app->make(ProductMappingCache::class)
            );

            // Register enabled strategies
            $service->registerStrategy(new StockSyncStrategy());

            return $service;
        });

        $this->app->singleton(LightspeedOrderSyncService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Initialize WooCommerce hooks for cache invalidation
        if (function_exists('WC')) {
            $cache = $this->app->make(ProductMappingCache::class);
            $cache->initializeHooks();
        }

        // Hook into WooCommerce order processing to sync to Lightspeed
        add_action('woocommerce_order_status_processing', function (int $orderId) {
            $orderSyncService = $this->app->make(LightspeedOrderSyncService::class);
            $orderSyncService->syncOrderToLightspeed($orderId);
        });
    }
}
