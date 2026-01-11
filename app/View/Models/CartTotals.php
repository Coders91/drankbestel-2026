<?php

namespace App\View\Models;

final readonly class CartTotals
{
    public function __construct(
        public int          $itemCount,

        public Price        $subtotal,
        public Price        $subtotalExclTax,
        public Price        $subtotalInclTax,
        public Price        $subtotalBeforeDiscounts,
        public Price        $subtotalAfterDiscounts,

        public Price        $shipping,
        public Price|string $shippingDisplay,
        public bool         $hasFreeShipping,

        public Price        $saleDiscount,
        public Price        $couponDiscount,
        public Price        $discount,
        public Price        $tax,
        public Price        $total,
    ) {}

    public static function fromCart(): self
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return self::empty();
        }

        $cart = WC()->cart;
        $cart->calculate_totals();

        // Calculate sale discount from cart items
        $saleDiscount = 0;
        $subtotalBeforeDiscounts = 0;

        foreach ($cart->get_cart() as $cartItem) {
            $product = $cartItem['data'];
            $quantity = $cartItem['quantity'];

            if ($product->is_on_sale() && $product->get_regular_price()) {
                $regularPrice = (float) $product->get_regular_price();
                $salePrice = (float) $product->get_price();
                $saleDiscount += ($regularPrice - $salePrice) * $quantity;
                $subtotalBeforeDiscounts += $regularPrice * $quantity;
            } else {
                $subtotalBeforeDiscounts += (float) $product->get_price() * $quantity;
            }
        }

        // Subtotals
        $subtotalExclTax = $cart->get_subtotal();
        $subtotalTax     = $cart->get_subtotal_tax();
        $subtotalInclTax = $subtotalExclTax + $subtotalTax;

        // Shipping
        $shippingExclTax = $cart->get_shipping_total();
        $hasFreeShipping = $shippingExclTax <= 0;

        // Discounts
        $couponDiscount = $cart->get_discount_total();
        $totalDiscount = $saleDiscount + $couponDiscount;
        $subtotalAfterDiscounts = $subtotalBeforeDiscounts - $totalDiscount;

        // Tax (products + shipping)
        $taxTotal =
            $cart->get_cart_contents_tax()
            + $cart->get_shipping_tax();

        return new self(
            itemCount: $cart->get_cart_contents_count(),

            subtotal: Price::from($subtotalInclTax),
            subtotalExclTax: Price::from($subtotalExclTax),
            subtotalInclTax: Price::from($subtotalInclTax),
            subtotalBeforeDiscounts: Price::from($subtotalBeforeDiscounts),
            subtotalAfterDiscounts: Price::from($subtotalAfterDiscounts),

            shipping: Price::from($shippingExclTax),
            shippingDisplay: $hasFreeShipping
                ? __('Gratis', 'sage')
                : Price::from($shippingExclTax),
            hasFreeShipping: $hasFreeShipping,

            saleDiscount: Price::from($saleDiscount),
            couponDiscount: Price::from($couponDiscount),
            discount: Price::from($totalDiscount),
            tax: Price::from($taxTotal),
            total: Price::from($cart->get_total('edit')),
        );
    }

    public static function empty(): self
    {
        return new self(
            itemCount: 0,

            subtotal: Price::from(0),
            subtotalExclTax: Price::from(0),
            subtotalInclTax: Price::from(0),
            subtotalBeforeDiscounts: Price::from(0),
            subtotalAfterDiscounts: Price::from(0),

            shipping: Price::from(0),
            shippingDisplay: Price::from(0),
            hasFreeShipping: false,

            saleDiscount: Price::from(0),
            couponDiscount: Price::from(0),
            discount: Price::from(0),
            tax: Price::from(0),
            total: Price::from(0),
        );
    }
}
