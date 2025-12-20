<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Woocommerce\Gateways\IdealGateway;

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
        //
    }
}
