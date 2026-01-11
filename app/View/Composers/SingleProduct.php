<?php

namespace App\View\Composers;

use App\View\Models\SingleProduct as SingleProductModel;
use Roots\Acorn\View\Composer;
use WC_Product;

class SingleProduct extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'woocommerce.content-single-product',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function override(): array
    {
        $product = $this->getProduct();

        if (! $product) {
            return [];
        }

        return [
            'product' => SingleProductModel::find($product),
        ];
    }

    /**
     * Get the WC_Product from global scope.
     */
    protected function getProduct(): ?WC_Product
    {
        global $product;

        return $product instanceof WC_Product ? $product : null;
    }
}
