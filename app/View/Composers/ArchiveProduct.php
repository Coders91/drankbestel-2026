<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use App\View\Models\Product;
use Log1x\Pagi\PagiFacade as Pagi;

class ArchiveProduct extends Composer
{
    protected static $views = [
        'woocommerce.archive-product',
    ];

    public function with(): array
    {
        global $wp_query;

        if (! $wp_query?->posts) {
            return [];
        }

        $pagination = Pagi::build();

        return [
            'products' => collect($wp_query->posts)
                ->map(fn ($post) => Product::find($post->ID))
                ->filter(),
            'pagination' => $pagination->links('components.pagination'),
        ];
    }
}
