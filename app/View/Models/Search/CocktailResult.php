<?php

namespace App\View\Models\Search;

use Livewire\Wireable;
use WP_Post;

readonly class CocktailResult implements Wireable
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $url,
        public ?string $excerpt,
        public int|string|null $imageId,
        public ?int $prepTime,
        public ?string $difficulty,
        public array $liquorTypes,
    ) {}

    public static function find(int|WP_Post $post): ?self
    {
        if ($post instanceof self) {
            return $post;
        }

        if (is_int($post)) {
            $post = get_post($post);
        }

        if (! $post instanceof WP_Post || $post->post_type !== 'cocktail') {
            return null;
        }

        if ($post->post_status !== 'publish') {
            return null;
        }

        // Get liquor types
        $liquorTypes = [];
        $terms = get_the_terms($post->ID, 'liquor_type');
        if ($terms && ! is_wp_error($terms)) {
            foreach ($terms as $term) {
                $liquorTypes[] = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
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
            prepTime: get_field('prep_time', $post->ID) ?: null,
            difficulty: get_field('difficulty', $post->ID) ?: null,
            liquorTypes: $liquorTypes,
        );
    }

    public function getDifficultyLabel(): ?string
    {
        return match ($this->difficulty) {
            'easy' => __('Makkelijk', 'sage'),
            'medium' => __('Gemiddeld', 'sage'),
            'hard' => __('Moeilijk', 'sage'),
            default => null,
        };
    }

    public function getPrepTimeFormatted(): ?string
    {
        if (! $this->prepTime) {
            return null;
        }

        return sprintf(__('%d min', 'sage'), $this->prepTime);
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
