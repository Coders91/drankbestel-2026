<?php

namespace App\View\Composers;

use App\Services\UspService;
use App\Services\Woocommerce\ShippingZoneService;
use Roots\Acorn\View\Composer;

class Usps extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        '*',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        return [
            'minAmount' => ShippingZoneService::freeShippingMinimum(),
            'isNextDayDelivery' => UspService::isNextDayDelivery(),
        ];
    }
}
