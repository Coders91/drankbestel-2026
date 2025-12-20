<?php

namespace app\View\Models;

use app\Services\Woocommerce\ShippingZoneService;

final readonly class FreeShippingProgress
{
    public function __construct(
        public float  $minimum,
        public Price $minimumFormatted,
        public float  $remaining,
        public Price $remainingFormatted,
        public int    $percentage,
        public bool   $qualifies,
    ) {}

    public static function calculate(float $subtotal): self
    {
        $minimum = ShippingZoneService::freeShippingMinimum();
        $remaining = max(0, $minimum - $subtotal);
        $percentage = $minimum > 0 ? min(100, (int) round(($subtotal / $minimum) * 100)) : 100;
        $qualifies = $subtotal >= $minimum;

        return new self(
            minimum: $minimum,
            minimumFormatted: Price::from($minimum),
            remaining: $remaining,
            remainingFormatted: Price::from($remaining),
            percentage: $percentage,
            qualifies: $qualifies,
        );
    }
}
