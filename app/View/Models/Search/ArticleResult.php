<?php

namespace App\View\Models\Search;

use Livewire\Wireable;
use WP_Post;

readonly class ArticleResult implements Wireable
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $url,
        public ?string $excerpt,
        public int|string|null $imageId,
        public string $contentFormat,
        public ?string $listVariant,
        public ?array $primaryCategory,
    ) {}

    public static function find(int|WP_Post $post): ?self
    {
        if ($post instanceof self) {
            return $post;
        }

        if (is_int($post)) {
            $post = get_post($post);
        }

        if (! $post instanceof WP_Post || $post->post_type !== 'article') {
            return null;
        }

        if ($post->post_status !== 'publish') {
            return null;
        }

        $contentFormat = get_field('content_format', $post->ID) ?: 'standard';
        $listVariant = $contentFormat === 'list' ? get_field('list_variant', $post->ID) : null;

        // Get primary category info
        $primaryCategoryId = get_field('primary_category', $post->ID);
        $primaryCategory = null;

        if ($primaryCategoryId) {
            $term = get_term($primaryCategoryId, 'product_cat');
            if ($term && ! is_wp_error($term)) {
                $primaryCategory = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'url' => get_term_link($term),
                ];
            }
        }

        return new self(
            id: $post->ID,
            title: $post->post_title,
            slug: $post->post_name,
            url: get_permalink($post),
            excerpt: $post->post_excerpt ?: wp_trim_words(strip_tags($post->post_content), 20),
            imageId: get_post_thumbnail_id($post) ?: null,
            contentFormat: $contentFormat,
            listVariant: $listVariant,
            primaryCategory: $primaryCategory,
        );
    }

    public function isListFormat(): bool
    {
        return $this->contentFormat === 'list';
    }

    public function getListVariantLabel(): ?string
    {
        if (! $this->isListFormat()) {
            return null;
        }

        return match ($this->listVariant) {
            'best' => __('Beste keuze', 'sage'),
            'cheapest' => __('Goedkoopste', 'sage'),
            'seasonal' => __('Seizoen', 'sage'),
            default => null,
        };
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
