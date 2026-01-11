<?php

namespace App\Services;

use App\Services\Woocommerce\ShippingZoneService;
use App\Support\Money;

final readonly class UspService
{
    protected const CUTOFF_TIME = '18:00';

    protected const DAYS = [
        1 => 'maandag',
        2 => 'dinsdag',
        3 => 'woensdag',
        4 => 'donderdag',
        5 => 'vrijdag',
        6 => 'zaterdag',
        7 => 'zondag',
    ];

    /**
     * Get USPs for the header bar slider
     */
    public static function headerUsps(): array
    {
        $freeShippingAmount = number_format(ShippingZoneService::freeShippingMinimum(), 0, ',', '.');
        $flatRateCost = Money::from(ShippingZoneService::flatRateCost())->formatted();

        return [
            [
                'title' => "Gratis verzending vanaf € {$freeShippingAmount},-",
                'icon' => 'truck-01',
                'description' => "Wanneer je voor € {$freeShippingAmount},- of meer bestelt, bezorgen we je bestelling gratis thuis. Voor bestellingen onder de € {$freeShippingAmount},- betaal je {$flatRateCost} verzendkosten.",
            ],
            [
                'title' => self::getDeliveryText(),
                'icon' => 'clock',
                'description' => 'Vanaf maandag tot en met vrijdag voor 18:00 besteld, is morgen in huis. Bestellingen gedaan op zaterdag of zondag worden dinsdag bezorgd.',
            ],
            [
                'title' => 'Kies zelf je bezorgdag',
                'icon' => 'calendar-check-01',
                'description' => 'Bij het afrekenen kan je zelf kiezen op welke dag je je bestelling wilt laten bezorgen. Handig als je zeker wilt weten dat je thuis bent op het moment van bezorging.',
            ],
        ];
    }

    /**
     * Get only USP titles
     */
    public static function headerUspsTitles(): array
    {
        return array_column(self::headerUsps(), 'title');
    }

    /**
     * Get USPs for the product page (trust badges)
     */
    public static function productUsps(): array
    {
        $freeShippingAmount = number_format(ShippingZoneService::freeShippingMinimum(), 0, ',', '.');

        return [
            [
                'title' => __('Gratis verzending', 'sage'),
                'subtitle' => __('Vanaf', 'sage') . " €{$freeShippingAmount}",
                'icon' => 'truck-01',
            ],
            [
                'title' => __('30 dagen retour', 'sage'),
                'subtitle' => __('Gratis retour binnen 30 dagen', 'sage'),
                'icon' => 'refresh-ccw-01',
            ],
            [
                'title' => __('Veilig betalen', 'sage'),
                'subtitle' => 'iDEAL, Creditcard, PayPal',
                'icon' => 'shield-tick',
            ],
            [
                'title' => __('Snel geleverd', 'sage'),
                'subtitle' => __('Binnen 1-3 werkdagen', 'sage'),
                'icon' => 'package',
            ],
        ];
    }

    /**
     * Get all USPs combined
     */
    public static function all(): array
    {
        return array_merge(self::headerUsps(), self::productUsps());
    }

    /**
     * Check if order placed now will be delivered next day
     */
    public static function isNextDayDelivery(): bool
    {
        $deliveryDay = self::getDeliveryDay();
        $tomorrow = current_time('timestamp') + DAY_IN_SECONDS;
        $tomorrowDayName = self::DAYS[date('N', $tomorrow)];

        return $deliveryDay === $tomorrowDayName;
    }

    /**
     * Get the delivery day name for an order placed now
     */
    public static function getDeliveryDay(): string
    {
        $currentTime = current_time('H:i');
        $currentDay = (int) current_time('N'); // 1 (Monday) through 7 (Sunday)

        return self::calculateDeliveryDay($currentDay, $currentTime);
    }

    /**
     * Get delivery text for display
     */
    public static function getDeliveryText(): string
    {
        $deliveryDay = self::getDeliveryDay();

        if (self::isNextDayDelivery()) {
            return 'Nu besteld, morgen in huis';
        }

        return "Nu besteld, {$deliveryDay} in huis";
    }

    /**
     * Calculate which day an order will be delivered
     */
    protected static function calculateDeliveryDay(int $currentDay, string $currentTime): string
    {
        $deliveryTimestamp = strtotime('tomorrow');
        $isAfterCutoff = $currentTime > self::CUTOFF_TIME;

        // Monday
        if ($currentDay === 1) {
            $deliveryTimestamp = $isAfterCutoff
                ? strtotime('+2 days')
                : strtotime('tomorrow');
        }
        // Tuesday through Thursday
        elseif ($currentDay >= 2 && $currentDay <= 4) {
            $deliveryTimestamp = $isAfterCutoff
                ? strtotime('+2 days')
                : strtotime('tomorrow');
        }
        // Friday
        elseif ($currentDay === 5) {
            $deliveryTimestamp = $isAfterCutoff
                ? strtotime('next tuesday')
                : strtotime('tomorrow');
        }
        // Saturday or Sunday
        else {
            $deliveryTimestamp = strtotime('next tuesday');
        }

        $deliveryDayNumber = (int) date('N', $deliveryTimestamp);

        return self::DAYS[$deliveryDayNumber];
    }

    /**
     * Get the cutoff time
     */
    public static function getCutoffTime(): string
    {
        return self::CUTOFF_TIME;
    }
}
