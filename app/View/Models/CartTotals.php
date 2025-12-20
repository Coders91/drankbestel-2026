<?php

namespace App\View\Models;

final readonly class CartTotals
{
    public function __construct(
        public int          $itemCount,

        public Price        $subtotal,
        public Price        $subtotalExclTax,
        public Price        $subtotalInclTax,

        public Price        $shipping,
        public Price|string $shippingDisplay,
        public bool         $hasFreeShipping,

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

        // Subtotals
        $subtotalExclTax = $cart->get_subtotal();
        $subtotalTax     = $cart->get_subtotal_tax();
        $subtotalInclTax = $subtotalExclTax + $subtotalTax;

        // Shipping
        $shippingExclTax = $cart->get_shipping_total();
        $hasFreeShipping = $shippingExclTax <= 0;

        // Tax (products + shipping)
        $taxTotal =
            $cart->get_cart_contents_tax()
            + $cart->get_shipping_tax();

        return new self(
            itemCount: $cart->get_cart_contents_count(),

            subtotal: Price::from($subtotalInclTax),
            subtotalExclTax: Price::from($subtotalExclTax),
            subtotalInclTax: Price::from($subtotalInclTax),

            shipping: Price::from($shippingExclTax),
            shippingDisplay: $hasFreeShipping
                ? __('Gratis', 'sage')
                : Price::from($shippingExclTax),
            hasFreeShipping: $hasFreeShipping,

            discount: Price::from($cart->get_discount_total()),
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

            shipping: Price::from(0),
            shippingDisplay: Price::from(0),
            hasFreeShipping: false,

            discount: Price::from(0),
            tax: Price::from(0),
            total: Price::from(0),
        );
    }
}
