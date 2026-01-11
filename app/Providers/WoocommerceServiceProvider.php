<?php

namespace App\Providers;

use App\Services\Woocommerce\ProductSlugService;
use Illuminate\Support\ServiceProvider;

class WoocommerceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        ProductSlugService::register();
    }
}
