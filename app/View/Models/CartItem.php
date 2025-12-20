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
        public Price   $price,
        public Price   $lineSubtotal,
        public Price   $lineTotal,
        public int     $maxQuantity,
        public bool    $isInStock,
    ) {}

    public static function fromCartItem(array $cartItem, string $cartItemKey): self
    {
        /** @var WC_Product $product */
        $product = $cartItem['data'];

        return new self(
            key: $cartItemKey,
            product: Product::find($product),
            quantity: $cartItem['quantity'],
            price: Price::from($product->get_price()),
            lineSubtotal: Price::from($cartItem['line_subtotal']),
            lineTotal: Price::from($cartItem['line_total']),
            maxQuantity: $product->get_stock_quantity() ?: 99,
            isInStock: $product->is_in_stock(),
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
