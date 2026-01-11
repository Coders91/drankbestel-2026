<?php

namespace App\Services\StructuredData;

use App\Services\StructuredData\Builders\ArticleBuilder;
use App\Services\StructuredData\Builders\BreadcrumbBuilder;
use App\Services\StructuredData\Builders\ItemListBuilder;
use App\Services\StructuredData\Builders\OrganizationBuilder;
use App\Services\StructuredData\Builders\ProductBuilder;
use App\Services\StructuredData\Builders\RecipeBuilder;
use App\Services\StructuredData\Builders\WebPageBuilder;
use App\Services\StructuredData\Builders\WebSiteBuilder;
use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use App\View\Models\Product;
use App\View\Models\SingleProduct;
use Spatie\SchemaOrg\Graph;
use WC_Product;

class StructuredDataService
{
    use HasSchemaIdentifiers;

    protected Graph $graph;

    public function __construct(
        protected OrganizationBuilder $organizationBuilder,
        protected WebSiteBuilder $webSiteBuilder,
        protected WebPageBuilder $webPageBuilder,
        protected BreadcrumbBuilder $breadcrumbBuilder,
        protected ProductBuilder $productBuilder,
        protected ItemListBuilder $itemListBuilder,
        protected ArticleBuilder $articleBuilder,
        protected RecipeBuilder $recipeBuilder,
    ) {}

    /**
     * Build the complete schema graph for the current page context.
     */
    public function build(): string
    {
        $this->graph = new Graph();

        // Build page-specific schemas based on context
        if ($this->isHomepage()) {
            // OnlineStore + WebSite with publisher only on homepage
            $this->addOrganization();
            $this->addWebSite(includePublisher: true);
            $this->buildHomepageSchema();
        } elseif (is_product()) {
            $this->addWebSite();
            $this->buildProductSchema();
        } elseif ($this->isProductArchive()) {
            $this->addWebSite();
            $this->buildArchiveSchema();
        } elseif (is_singular('article')) {
            $this->addWebSite();
            $this->buildArticleSchema();
        } elseif (is_singular('cocktail')) {
            $this->addWebSite();
            $this->buildCocktailSchema();
        } else {
            $this->addWebSite();
            $this->buildGenericPageSchema();
        }

        return $this->graph->toScript();
    }

    protected function isHomepage(): bool
    {
        return is_front_page() || is_home();
    }

    protected function isProductArchive(): bool
    {
        return is_shop() || is_product_category() || is_product_tag();
    }

    protected function isKlantenservice(): bool
    {
        return is_singular('klantenservice');
    }

    protected function addOrganization(): void
    {
        $organization = $this->organizationBuilder->build();
        $this->graph->add($organization);
    }

    protected function addWebSite(bool $includePublisher = false): void
    {
        $webSite = $this->webSiteBuilder->build($includePublisher);
        $this->graph->add($webSite);
    }

    protected function buildHomepageSchema(): void
    {
        $webPage = $this->webPageBuilder->build(
            pageType: 'WebPage',
            name: get_bloginfo('name'),
            description: get_bloginfo('description'),
        );
        $this->graph->add($webPage);
    }

    protected function buildProductSchema(): void
    {
        global $product;

        if (! $product instanceof WC_Product) {
            $this->buildGenericPageSchema();

            return;
        }

        $singleProduct = SingleProduct::find($product);

        if (! $singleProduct) {
            $this->buildGenericPageSchema();

            return;
        }

        // Product schema
        $productSchema = $this->productBuilder->build($singleProduct);
        $this->graph->add($productSchema);

        // WebPage with mainEntity reference to Product
        $webPage = $this->webPageBuilder->build(
            pageType: 'ItemPage',
            name: $singleProduct->title,
            description: wp_strip_all_tags($singleProduct->shortDescription ?: $singleProduct->description),
            mainEntityId: $this->productId($singleProduct->id),
        );
        $this->graph->add($webPage);

        // Breadcrumbs
        $breadcrumbs = $this->breadcrumbBuilder->build();
        if ($breadcrumbs) {
            $this->graph->add($breadcrumbs);
        }
    }

    protected function buildArchiveSchema(): void
    {
        global $wp_query;

        // Get products from current query
        $products = collect($wp_query->posts ?? [])
            ->map(fn ($post) => Product::find($post->ID))
            ->filter();

        if ($products->isNotEmpty()) {
            $itemList = $this->itemListBuilder->build($products);
            $this->graph->add($itemList);
        }

        // Determine page name
        $pageName = is_shop()
            ? get_the_title(wc_get_page_id('shop'))
            : (get_queried_object()->name ?? __('Producten', 'sage'));

        // WebPage for collection
        $webPage = $this->webPageBuilder->build(
            pageType: 'CollectionPage',
            name: $pageName,
            description: wp_strip_all_tags(term_description() ?: ''),
        );
        $this->graph->add($webPage);

        // Breadcrumbs
        $breadcrumbs = $this->breadcrumbBuilder->build();
        if ($breadcrumbs) {
            $this->graph->add($breadcrumbs);
        }
    }

    protected function buildGenericPageSchema(): void
    {
        // Determine page name
        $name = '';
        if (is_singular()) {
            $name = get_the_title();
        } elseif (is_archive()) {
            $name = get_the_archive_title();
        } elseif (is_search()) {
            $name = sprintf(__('Zoekresultaten voor: %s', 'sage'), get_search_query());
        }

        // WebPage
        $webPage = $this->webPageBuilder->build(
            pageType: 'WebPage',
            name: $name ?: get_bloginfo('name'),
            description: is_singular() ? wp_strip_all_tags(get_the_excerpt()) : '',
        );
        $this->graph->add($webPage);

        // Breadcrumbs (skip on homepage)
        if (! $this->isHomepage()) {
            $breadcrumbs = $this->breadcrumbBuilder->build();
            if ($breadcrumbs) {
                $this->graph->add($breadcrumbs);
            }
        }
    }

    protected function buildArticleSchema(): void
    {
        $post = get_post();

        if (! $post || $post->post_type !== 'article') {
            $this->buildGenericPageSchema();

            return;
        }

        $contentFormat = get_field('content_format', $post->ID) ?: 'standard';

        // Build Article schema (always)
        $articleSchema = $this->articleBuilder->build($post);
        $this->graph->add($articleSchema);

        // Build ItemList schema for list-format articles
        if ($contentFormat === 'list') {
            $listSchema = $this->articleBuilder->buildList($post);
            $this->graph->add($listSchema);
        }

        // WebPage with mainEntity reference to Article
        $webPage = $this->webPageBuilder->build(
            pageType: 'ArticlePage',
            name: $post->post_title,
            description: wp_strip_all_tags($post->post_excerpt ?: wp_trim_words($post->post_content, 55)),
            mainEntityId: trailingslashit(get_permalink($post)) . '#article',
        );
        $this->graph->add($webPage);

        // Breadcrumbs
        $breadcrumbs = $this->breadcrumbBuilder->build();
        if ($breadcrumbs) {
            $this->graph->add($breadcrumbs);
        }
    }

    protected function buildCocktailSchema(): void
    {
        $post = get_post();

        if (! $post || $post->post_type !== 'cocktail') {
            $this->buildGenericPageSchema();

            return;
        }

        // Build Recipe schema
        $recipeSchema = $this->recipeBuilder->build($post);
        $this->graph->add($recipeSchema);

        // WebPage with mainEntity reference to Recipe
        $webPage = $this->webPageBuilder->build(
            pageType: 'ItemPage',
            name: $post->post_title,
            description: wp_strip_all_tags($post->post_excerpt ?: wp_trim_words($post->post_content, 55)),
            mainEntityId: trailingslashit(get_permalink($post)) . '#recipe',
        );
        $this->graph->add($webPage);

        // Breadcrumbs
        $breadcrumbs = $this->breadcrumbBuilder->build();
        if ($breadcrumbs) {
            $this->graph->add($breadcrumbs);
        }
    }
}
