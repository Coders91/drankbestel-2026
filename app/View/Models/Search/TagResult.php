<?php

namespace App\View\Models\Search;

use Livewire\Wireable;
use WP_Term;

readonly class TagResult implements Wireable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $url,
        public int $count,
    ) {}

    public static function find(int|WP_Term $term): ?self
    {
        if ($term instanceof self) {
            return $term;
        }

        if (is_int($term)) {
            $term = get_term($term, 'product_tag');
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
            count: $term->count,
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
