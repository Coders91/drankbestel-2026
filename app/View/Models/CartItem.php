<?php

namespace App\View\Models;

use Livewire\Wireable;
use WC_Product;

final class CartItem implements Wireable
{
    public function __construct(
        public string  $key,
        public Product $product,
        public int     $quantity,
        public ?Price  $lineRegularTotal,
        public Price   $lineSubtotal,
        public Price   $lineTotal,
        public int     $minQuantity,
        public int     $maxQuantity,
        public bool    $isInStock,
        public bool    $soldAsPack = false,
        public int     $packSize = 1,
    ) {}

    public static function fromCartItem(array $cartItem, string $cartItemKey): self
    {
        $wc_product = $cartItem['data'];
        /** @var Product $product */
        $product = Product::find($wc_product);

        $regularTotal = $wc_product->get_regular_price() * $cartItem['quantity'];

        return new self(
            key: $cartItemKey,
            product: $product,
            quantity: $cartItem['quantity'],
            lineRegularTotal: Price::from($regularTotal),
            lineSubtotal: Price::from($cartItem['line_subtotal']),
            lineTotal: Price::from($cartItem['line_total']),
            minQuantity: $product->soldAsPack ? $product->packSize : 1,
            maxQuantity: $product->stockQuantity ?: 99,
            isInStock: $product->is_in_stock,
            soldAsPack: $product->soldAsPack,
            packSize: $product->packSize,
        );
    }

    public function toLivewire(): array
    {
        return ['key' => $this->key];
    }

    public static function fromLivewire($value): ?self
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return null;
        }

        $cartItem = WC()->cart->get_cart_item($value['key']);

        return $cartItem ? self::fromCartItem($cartItem, $value['key']) : null;
    }
}
