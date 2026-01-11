<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Closure;
use App\View\Models\Product;

class Breadcrumbs extends Component
{
    public array $crumbs = [];

    public function __construct()
    {
        $this->crumbs = $this->generateBreadcrumbs();
    }

    protected function generateBreadcrumbs(): array
    {
        $crumbs = [
            [
                'name' => get_svg(
                    'resources.images.icons.home-03',
                    'w-5 h-5 stroke-gray-700',
                    ['aria-hidden' => 'true']
                ),
                'url' => home_url('/'),
            ],
        ];

        if (is_product()) {
            return array_merge($crumbs, $this->productCrumbs());
        }

        if (is_product_category()) {
            return array_merge($crumbs, $this->productCategoryCrumbs());
        }

        return array_merge($crumbs, $this->urlCrumbs());
    }

    /**
     * Breadcrumbs for single products
     */
    protected function productCrumbs(): array
    {
        $crumbs = [];

        $product = Product::find(get_the_ID());

        $categories = $product->categories;

        if (!empty($categories)) {
            // Safely get the deepest category (readonly-safe)
            $primary = $categories[count($categories) - 1];

            // Parent hierarchy
            $ancestors = array_reverse(
                get_ancestors($primary->term_id, 'product_cat')
            );

            foreach ($ancestors as $ancestorId) {
                $term = get_term($ancestorId, 'product_cat');
                $crumbs[] = [
                    'name' => $term->name,
                    'url'  => get_term_link($term),
                ];
            }

            // Primary category
            $crumbs[] = [
                'name' => $primary->name,
                'url'  => get_term_link($primary),
            ];
        }

        // Product title (current page)
        $crumbs[] = [
            'name' => $product->title,
            'url'  => null,
        ];

        return $crumbs;
    }

    /**
     * Breadcrumbs for product category archives
     */
    protected function productCategoryCrumbs(): array
    {
        $crumbs = [];

        $term = get_queried_object();

        // Parent categories
        $ancestors = array_reverse(
            get_ancestors($term->term_id, 'product_cat')
        );

        foreach ($ancestors as $ancestorId) {
            $ancestor = get_term($ancestorId, 'product_cat');
            $crumbs[] = [
                'name' => $ancestor->name,
                'url'  => get_term_link($ancestor),
            ];
        }

        // Current category (no link)
        $crumbs[] = [
            'name' => $term->name,
            'url'  => null,
        ];

        return $crumbs;
    }

    /**
     * Fallback URL-based breadcrumbs
     */
    protected function urlCrumbs(): array
    {
        $crumbs = [];

        $urlPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', $urlPath)));
        $homeUrl  = home_url('/');
        $path     = '';

        foreach ($segments as $i => $segment) {
            // Pagination
            if ($segment === 'page' && isset($segments[$i + 1])) {
            $crumbs[] = [
                    'name' => 'Pagina ' . (int) $segments[$i + 1],
                    'url'  => null,
                ];
                break;
            }

            $path .= $segment . '/';
            $isLast = $i === array_key_last($segments);

            if ($isLast) {
                if (is_tax()) {
                    $term  = get_queried_object();
                    $title = $term->name ?? ucfirst(str_replace('-', ' ', $segment));
                } elseif (is_singular()) {
                    $title = get_the_title();
                } else {
                    $title = ucfirst(str_replace('-', ' ', $segment));
                }
            } else {
                $title = ucfirst(str_replace('-', ' ', $segment));
            }

            $crumbs[] = [
                'name' => $title,
                'url'  => $isLast ? null : trailingslashit($homeUrl . $path),
            ];
        }

        return $crumbs;
    }

    public function render(): View|Closure|string
    {
        return view('components.breadcrumbs');
    }
}
