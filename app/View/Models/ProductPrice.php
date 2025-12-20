<?php

namespace App\View\Models;

use App\Support\Money;
use WC_Product;

readonly class ProductPrice
{
    public function __construct(
        public Money $regular,
        public ?Money $sale,
        public bool $is_on_sale,
    ) {}

    public static function fromProduct(WC_Product $product): self
    {
        $regular = Money::from($product->get_regular_price());
        $sale    = $product->is_on_sale()
            ? Money::from($product->get_sale_price())
            : null;

        return new self(
            regular: $regular,
            sale: $sale,
            is_on_sale: $product->is_on_sale(),
        );
    }
}
