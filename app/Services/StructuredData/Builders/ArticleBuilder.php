<?php

namespace App\Services\StructuredData\Builders;

use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use Spatie\SchemaOrg\Article;
use Spatie\SchemaOrg\ItemList;
use Spatie\SchemaOrg\Schema;
use WP_Post;

class ArticleBuilder
{
    use HasSchemaIdentifiers;

    /**
     * Build Article schema for a standard article.
     */
    public function build(WP_Post $post): Article
    {
        $schema = Schema::article()
            ->setProperty('@id', $this->articleId($post->ID))
            ->headline($post->post_title)
            ->url(get_permalink($post))
            ->datePublished(get_the_date('c', $post))
            ->dateModified(get_the_modified_date('c', $post));

        // Description
        $description = $post->post_excerpt ?: wp_trim_words(strip_tags($post->post_content), 55);
        if ($description) {
            $schema->description($description);
        }

        // Author
        $authorName = get_the_author_meta('display_name', $post->post_author);
        if ($authorName) {
            $schema->author(
                Schema::person()
                    ->name($authorName)
                    ->url(get_author_posts_url($post->post_author))
            );
        }

        // Publisher
        $schema->publisher(['@id' => $this->organizationId()]);

        // Image
        $imageId = get_post_thumbnail_id($post);
        if ($imageId) {
            $imageUrl = wp_get_attachment_image_url($imageId, 'large');
            if ($imageUrl) {
                $schema->image($imageUrl);
            }
        }

        // Main entity of page
        $schema->mainEntityOfPage(['@id' => $this->webPageId(get_permalink($post))]);

        // Article section (primary category)
        $primaryCategoryId = get_field('primary_category', $post->ID);
        if ($primaryCategoryId) {
            $term = get_term($primaryCategoryId, 'product_cat');
            if ($term && ! is_wp_error($term)) {
                $schema->articleSection($term->name);
            }
        }

        return $schema;
    }

    /**
     * Build ItemList schema for list-format articles (top 10, best of, etc.).
     */
    public function buildList(WP_Post $post): ItemList
    {
        $listItems = get_field('list_items', $post->ID) ?: [];

        $schema = Schema::itemList()
            ->setProperty('@id', $this->articleId($post->ID) . '-list')
            ->name($post->post_title)
            ->url(get_permalink($post))
            ->numberOfItems(count($listItems));

        // Description
        $description = $post->post_excerpt ?: wp_trim_words(strip_tags($post->post_content), 55);
        if ($description) {
            $schema->description($description);
        }

        // Build list items
        $itemListElements = [];
        foreach ($listItems as $index => $item) {
            $position = $item['position'] ?? ($index + 1);
            $productId = $item['product'] ?? null;

            if ($productId) {
                $productUrl = get_permalink($productId);
                $productTitle = get_the_title($productId);

                $itemListElements[] = Schema::listItem()
                    ->position((int) $position)
                    ->url($productUrl)
                    ->name($productTitle)
                    ->item([
                        '@type' => 'Product',
                        'name' => $productTitle,
                        'url' => $productUrl,
                    ]);
            }
        }

        if (! empty($itemListElements)) {
            $schema->itemListElement($itemListElements);
        }

        return $schema;
    }

    /**
     * Build combined schema for list articles (Article + ItemList).
     */
    public function buildListArticle(WP_Post $post): array
    {
        return [
            $this->build($post),
            $this->buildList($post),
        ];
    }

    protected function articleId(int $postId): string
    {
        return trailingslashit(get_permalink($postId)) . '#article';
    }
}
