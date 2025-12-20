<?php

namespace App\View\Models;

use App\View\Models\Price;

final class OrderItem
{
    public function __construct(
        public Product $product,
        public int $quantity,
        public Price $subtotal,
        public Price $total,
    ) {}
}
