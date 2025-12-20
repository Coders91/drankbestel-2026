<?php

namespace App\View\Models;

use App\View\Models\Price;
use WC_Coupon;

final readonly class AppliedCoupon
{
    public function __construct(
        public string $code,
        public float  $amount,
        public Price $amountFormatted,
    ) {}

    public static function fromCode(string $couponCode): self
    {
        $coupon = new WC_Coupon($couponCode);
        $amount = WC()->cart->get_coupon_discount_amount($couponCode);

        return new self(
            code: $couponCode,
            amount: $amount,
            amountFormatted: Price::from($amount),
        );
    }

    /**
     * Get all applied coupons from cart
     *
     * @return array<AppliedCoupon>
     */
    public static function allFromCart(): array
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return [];
        }

        $coupons = [];

        foreach (WC()->cart->get_applied_coupons() as $couponCode) {
            $coupons[] = self::fromCode($couponCode);
        }

        return $coupons;
    }
}
