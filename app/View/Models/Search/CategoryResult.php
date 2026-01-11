<?php

namespace App\View\Models\Search;

use Livewire\Wireable;
use WP_Term;

readonly class CategoryResult implements Wireable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $url,
        public ?string $description,
        public int $count,
        public int|string|null $imageId,
    ) {}

    public static function find(int|WP_Term $term): ?self
    {
        if ($term instanceof self) {
            return $term;
        }

        if (is_int($term)) {
            $term = get_term($term, 'product_cat');
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
            count: $term->count,
            imageId: get_term_meta($term->term_id, 'thumbnail_id', true) ?: null,
        );
    }

    public function toLivewire(): array
    {
        return ['id' => $this->id];
    }

    public static function fromLivewire($value): ?self
    {
        return self::find($value['id'] ?? $value);
    }
}
