<?php

namespace App\Services\Woocommerce;

use Illuminate\Support\Facades\Cache;
use WC_Shipping_Zones;

final readonly class ShippingZoneService
{
    /**
     * Get the free shipping minimum amount from WooCommerce settings
     * Falls back to theme option or default value
     */
    public static function freeShippingMinimum(): float
    {
        return Cache::remember('free_shipping_amount', 43200, function () {
            $shipping_zones = WC_Shipping_Zones::get_zones();

            foreach ($shipping_zones as $zone) {
                foreach ($zone['shipping_methods'] as $method) {
                    if ($method->id === 'free_shipping' && $method->enabled === 'yes') {
                        $minAmount = $method->get_option('min_amount');
                        if ($minAmount) {
                            return (float) $minAmount;
                        }
                    }
                }
            }

            return (float) get_option('free_shipping_minimum', 100);
        });
    }

    public static function flatRateCost(): float
    {
        return Cache::remember('flat_rate_cost', 43200, function () {
            $shipping_zones = WC_Shipping_Zones::get_zones();

            foreach ($shipping_zones as $zone) {
                foreach ($zone['shipping_methods'] as $method) {
                    if ($method->id === 'flat_rate' && $method->enabled === 'yes') {
                        $cost = $method->get_option('cost');
                        if ($cost) {
                            return (float) $cost;
                        }
                    }
                }
            }

            return (float) get_option('flat_rate_costs', 7.95);
        });
    }
}
