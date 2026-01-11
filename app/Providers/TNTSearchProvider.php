<?php

namespace App\Providers;

use App\Services\Search\SearchAnalyticsService;
use App\Services\Search\SearchConfig;
use App\Services\Search\TNTSearchService;
use Illuminate\Support\ServiceProvider;
use TeamTNT\TNTSearch\TNTSearch;

class TNTSearchProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register SearchConfig
        $this->app->singleton(SearchConfig::class);

        // Register SearchAnalyticsService
        $this->app->singleton(SearchAnalyticsService::class, function () {
            // Only instantiate if table exists
            if (SearchAnalyticsService::tableExists()) {
                return new SearchAnalyticsService();
            }
            return null;
        });

        // Register TNTSearch
        $this->app->singleton(TNTSearch::class, function ($app) {
            $config = $app->make(SearchConfig::class);
            $tnt = new TNTSearch;

            $storagePath = $config->storagePath();
            if (!file_exists($storagePath)) {
                wp_mkdir_p($storagePath);
            }

            $tnt->loadConfig([
                'driver' => 'mysql',
                'host' => DB_HOST,
                'database' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASSWORD,
                'storage' => $storagePath,
            ]);

            return $tnt;
        });

        // Register TNTSearchService
        $this->app->singleton(TNTSearchService::class, function ($app) {
            return new TNTSearchService(
                $app->make(TNTSearch::class),
                $app->make(SearchConfig::class),
                $app->make(SearchAnalyticsService::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Real-time product index updates
        add_action('save_post_product', [$this, 'updateProductIndex'], 10, 1);
        add_action('before_delete_post', [$this, 'deleteFromProductIndex'], 10, 1);

        // Real-time category index updates
        add_action('created_product_cat', [$this, 'updateCategoryIndex'], 10, 1);
        add_action('edited_product_cat', [$this, 'updateCategoryIndex'], 10, 1);
        add_action('delete_product_cat', [$this, 'deleteCategoryFromIndex'], 10, 1);

        // Real-time tag index updates
        add_action('created_product_tag', [$this, 'updateTagIndex'], 10, 1);
        add_action('edited_product_tag', [$this, 'updateTagIndex'], 10, 1);
        add_action('delete_product_tag', [$this, 'deleteTagFromIndex'], 10, 1);

        // Real-time brand index updates
        $brandTaxonomies = config('search.indexes.brands.taxonomies', ['product_brand']);
        foreach ($brandTaxonomies as $taxonomy) {
            add_action("created_{$taxonomy}", [$this, 'updateBrandIndex'], 10, 1);
            add_action("edited_{$taxonomy}", [$this, 'updateBrandIndex'], 10, 1);
            add_action("delete_{$taxonomy}", [$this, 'deleteBrandFromIndex'], 10, 1);
        }

        // Real-time article index updates
        add_action('save_post_article', [$this, 'updateArticleIndex'], 10, 1);
        add_action('before_delete_post', [$this, 'deleteFromArticleIndex'], 10, 1);

        // Real-time cocktail index updates
        add_action('save_post_cocktail', [$this, 'updateCocktailIndex'], 10, 1);
        add_action('before_delete_post', [$this, 'deleteFromCocktailIndex'], 10, 1);
    }

    public function updateProductIndex(int $postId): void
    {
        try {
            $tnt = $this->app->make(TNTSearch::class);
            $tnt->selectIndex(config('search.indexes.products.file', 'products.index'));
            $index = $tnt->getIndex();

            $product = get_post($postId);
            $wcProduct = wc_get_product($postId);

            if ($product->post_status === 'publish' && $wcProduct) {
                $index->update($postId, [
                    'id' => $postId,
                    'title' => $product->post_title,
                    'excerpt' => $product->post_excerpt,
                    'content' => $product->post_content,
                    'sku' => $wcProduct->get_sku() ?? '',
                    'contents' => get_field('product_contents', $postId) ?? '',
                    'attributes' => $this->getProductAttributeNames($wcProduct),
                ]);
            } else {
                $index->delete($postId);
            }
        } catch (\Exception $e) {
            // Index may not exist yet, silently fail
        }
    }

    public function deleteFromProductIndex(int $postId): void
    {
        if (get_post_type($postId) !== 'product') {
            return;
        }

        try {
            $tnt = $this->app->make(TNTSearch::class);
            $tnt->selectIndex(config('search.indexes.products.file', 'products.index'));
            $index = $tnt->getIndex();
            $index->delete($postId);
        } catch (\Exception $e) {
            // Index may not exist yet, silently fail
        }
    }

    public function updateCategoryIndex(int $termId): void
    {
        $this->updateTaxonomyIndex($termId, 'product_cat', 'categories');
    }

    public function deleteCategoryFromIndex(int $termId): void
    {
        $this->deleteFromTaxonomyIndex($termId, 'categories');
    }

    public function updateTagIndex(int $termId): void
    {
        $this->updateTaxonomyIndex($termId, 'product_tag', 'tags');
    }

    public function deleteTagFromIndex(int $termId): void
    {
        $this->deleteFromTaxonomyIndex($termId, 'tags');
    }

    public function updateBrandIndex(int $termId): void
    {
        $this->updateTaxonomyIndex($termId, null, 'brands');
    }

    public function deleteBrandFromIndex(int $termId): void
    {
        $this->deleteFromTaxonomyIndex($termId, 'brands');
    }

    protected function updateTaxonomyIndex(int $termId, ?string $taxonomy, string $indexType): void
    {
        try {
            $tnt = $this->app->make(TNTSearch::class);
            $tnt->selectIndex(config("search.indexes.{$indexType}.file", "{$indexType}.index"));
            $index = $tnt->getIndex();

            $term = get_term($termId);
            if ($term && !is_wp_error($term) && $term->count > 0) {
                $index->update($termId, [
                    'id' => $termId,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description ?? '',
                ]);
            } else {
                $index->delete($termId);
            }
        } catch (\Exception $e) {
            // Index may not exist yet, silently fail
        }
    }

    protected function deleteFromTaxonomyIndex(int $termId, string $indexType): void
    {
        try {
            $tnt = $this->app->make(TNTSearch::class);
            $tnt->selectIndex(config("search.indexes.{$indexType}.file", "{$indexType}.index"));
            $index = $tnt->getIndex();
            $index->delete($termId);
        } catch (\Exception $e) {
            // Index may not exist yet, silently fail
        }
    }

    protected function getProductAttributeNames(\WC_Product $product): string
    {
        $attributes = $product->get_attributes();
        $names = [];

        foreach ($attributes as $attribute) {
            if ($attribute->is_taxonomy()) {
                $terms = wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
                $names = array_merge($names, $terms);
            } else {
                $names = array_merge($names, $attribute->get_options());
            }
        }

        return implode(' ', $names);
    }

    public function updateArticleIndex(int $postId): void
    {
        try {
            $tnt = $this->app->make(TNTSearch::class);
            $tnt->selectIndex(config('search.indexes.articles.file', 'articles.index'));
            $index = $tnt->getIndex();

            $post = get_post($postId);

            if ($post->post_status === 'publish') {
                // Get primary category name
                $primaryCategoryId = get_field('primary_category', $postId);
                $categoryName = '';
                if ($primaryCategoryId) {
                    $term = get_term($primaryCategoryId, 'product_cat');
                    if ($term && !is_wp_error($term)) {
                        $categoryName = $term->name;
                    }
                }

                $index->update($postId, [
                    'id' => $postId,
                    'title' => $post->post_title,
                    'excerpt' => $post->post_excerpt,
                    'content' => $post->post_content,
                    'category' => $categoryName,
                ]);
            } else {
                $index->delete($postId);
            }
        } catch (\Exception $e) {
            // Index may not exist yet, silently fail
        }
    }

    public function deleteFromArticleIndex(int $postId): void
    {
        if (get_post_type($postId) !== 'article') {
            return;
        }

        try {
            $tnt = $this->app->make(TNTSearch::class);
            $tnt->selectIndex(config('search.indexes.articles.file', 'articles.index'));
            $index = $tnt->getIndex();
            $index->delete($postId);
        } catch (\Exception $e) {
            // Index may not exist yet, silently fail
        }
    }

    public function updateCocktailIndex(int $postId): void
    {
        try {
            $tnt = $this->app->make(TNTSearch::class);
            $tnt->selectIndex(config('search.indexes.cocktails.file', 'cocktails.index'));
            $index = $tnt->getIndex();

            $post = get_post($postId);

            if ($post->post_status === 'publish') {
                // Get liquor type and cocktail type terms
                $termNames = [];
                foreach (['liquor_type', 'cocktail_type'] as $taxonomy) {
                    $terms = get_the_terms($postId, $taxonomy);
                    if ($terms && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $termNames[] = $term->name;
                        }
                    }
                }

                $index->update($postId, [
                    'id' => $postId,
                    'title' => $post->post_title,
                    'excerpt' => $post->post_excerpt,
                    'content' => $post->post_content,
                    'terms' => implode(' ', $termNames),
                ]);
            } else {
                $index->delete($postId);
            }
        } catch (\Exception $e) {
            // Index may not exist yet, silently fail
        }
    }

    public function deleteFromCocktailIndex(int $postId): void
    {
        if (get_post_type($postId) !== 'cocktail') {
            return;
        }

        try {
            $tnt = $this->app->make(TNTSearch::class);
            $tnt->selectIndex(config('search.indexes.cocktails.file', 'cocktails.index'));
            $index = $tnt->getIndex();
            $index->delete($postId);
        } catch (\Exception $e) {
            // Index may not exist yet, silently fail
        }
    }
}
