<?php

namespace App\View\Models;

use App\View\Models\Product;

readonly class InvoiceItem
{
    public function __construct(
        public Product $product,
        public string $name,
        public int $quantity,
        public Price $unit_price,
        public Price $subtotal,
        public Price $tax,
        public Price $total,
        public string $sku,
        public string $tax_rate,
    ) {}
}
