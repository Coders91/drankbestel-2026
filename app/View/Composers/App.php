<?php

namespace app\View\Composers;

use Roots\Acorn\View\Composer;

use app\Services\Woocommerce\ShippingZoneService;
use App\Support\Money;
class App extends Composer
{

    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '*',
    ];

    /**
     * Retrieve the site name.
     */
    public function siteName(): string
    {
        return get_bloginfo('name', 'display');
    }

    public function freeShippingMinimum(): string
    {
        return Money::from(ShippingZoneService::freeShippingMinimum())->formatted();
    }
}
