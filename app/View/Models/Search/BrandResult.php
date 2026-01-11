<?php

namespace App\View\Models\Search;

use Livewire\Wireable;
use WP_Term;

readonly class BrandResult implements Wireable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $url,
        public ?string $description,
        public int|string|null $imageId,
        public int $productCount,
        public string $taxonomy,
    ) {}

    public static function find(int|WP_Term $term, ?string $taxonomy = null): ?self
    {
        if ($term instanceof self) {
            return $term;
        }

        // If we have an ID, try configured brand taxonomies
        if (is_int($term)) {
            $taxonomies = $taxonomy ? [$taxonomy] : config('search.indexes.brands.taxonomies', ['product_brand']);

            foreach ($taxonomies as $tax) {
                $found = get_term($term, $tax);
                if ($found instanceof WP_Term && !is_wp_error($found)) {
                    $term = $found;
                    $taxonomy = $tax;
                    break;
                }
            }
        }

        if (!$term instanceof WP_Term || is_wp_error($term)) {
            return null;
        }

        $url = get_term_link($term);
        if (is_wp_error($url)) {
            return null;
        }

        return new self(
            id: $term->term_id,
            name: $term->name,
            slug: $term->slug,
            url: $url,
            description: $term->description ?: null,
            imageId: get_term_meta($term->term_id, 'thumbnail_id', true) ?: null,
            productCount: $term->count,
            taxonomy: $term->taxonomy,
        );
    }

    public function toLivewire(): array
    {
        return [
            'id' => $this->id,
            'taxonomy' => $this->taxonomy,
        ];
    }

    public static function fromLivewire($value): ?self
    {
        return self::find($value['id'] ?? $value, $value['taxonomy'] ?? null);
    }
}
