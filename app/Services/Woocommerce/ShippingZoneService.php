<?php

namespace app\Services\Woocommerce;

use Illuminate\Support\Facades\Cache;
use WC_Shipping_Zones;

final readonly class ShippingZoneService {

    /**
     * Get the free shipping minimum amount from WooCommerce settings
     * Falls back to theme option or default value
     */
    public static function freeShippingMinimum(): float
    {
        $shipping_zones = WC_Shipping_Zones::get_zones();
        return Cache::remember('free_shipping_amount', 43200 , function () use ($shipping_zones) {
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
            $themeOption = get_option('free_shipping_minimum', 100);
            return (float) $themeOption;
        });
    }

    public static function flatRateCost(): float
    {
        $shipping_zones = WC_Shipping_Zones::get_zones();
        return Cache::remember('free_shipping_amount', 43200 , function () use ($shipping_zones) {
            foreach ($shipping_zones as $zone) {
                foreach ($zone['shipping_methods'] as $method) {
                    if ($method->id === 'flate_rate' && $method->enabled === 'yes') {
                        $costs = $method->get_option('costs');
                        if ($costs) {
                            return (float) $costs;
                        }
                    }
                }
            }
            $themeOption = get_option('flat_rate_costs', 7.95);
            return (float) $themeOption;
        });
    }
}

