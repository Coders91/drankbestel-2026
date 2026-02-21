<?php

namespace App\Providers;

use App\Services\Woocommerce\ProductBaseRemovalService;
use App\Services\Woocommerce\ProductSlugService;
use Illuminate\Support\ServiceProvider;

class WoocommerceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        ProductSlugService::register();
        ProductBaseRemovalService::register();
    }
}
