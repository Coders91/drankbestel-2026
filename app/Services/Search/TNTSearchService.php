<?php

namespace App\Services\Search;

use App\Support\Search\SynonymsHandler;
use App\View\Models\Product;
use App\View\Models\Search\ArticleResult;
use App\View\Models\Search\BrandResult;
use App\View\Models\Search\CategoryResult;
use App\View\Models\Search\CocktailResult;
use App\View\Models\Search\SearchResult;
use App\View\Models\Search\TagResult;
use TeamTNT\TNTSearch\TNTSearch;
use Throwable;

class TNTSearchService
{
    public function __construct(
        protected TNTSearch $tnt,
        protected SearchConfig $config,
        protected ?SearchAnalyticsService $analytics = null,
    ) {}

    /**
     * Perform multi-type search
     */
    public function search(string $query, array $options = []): SearchResult
    {
        $query = trim($query);

        if (strlen($query) < $this->config->minQueryLength()) {
            return new SearchResult($query);
        }

        $limits = array_merge([
            'products' => $this->config->getLimit('products'),
            'categories' => $this->config->getLimit('categories'),
            'tags' => $this->config->getLimit('tags'),
            'brands' => $this->config->getLimit('brands'),
            'articles' => $this->config->getLimit('articles'),
            'cocktails' => $this->config->getLimit('cocktails'),
        ], $options['limits'] ?? []);

        $enabledTypes = $options['types'] ?? ['products', 'categories', 'tags', 'brands', 'articles', 'cocktails'];

        $result = new SearchResult(
            query: $query,
            products: in_array('products', $enabledTypes)
                ? $this->searchProducts($query, $limits['products'])
                : [],
            categories: in_array('categories', $enabledTypes)
                ? $this->searchCategories($query, $limits['categories'])
                : [],
            tags: in_array('tags', $enabledTypes)
                ? $this->searchTags($query, $limits['tags'])
                : [],
            brands: in_array('brands', $enabledTypes)
                ? $this->searchBrands($query, $limits['brands'])
                : [],
            articles: in_array('articles', $enabledTypes)
                ? $this->searchArticles($query, $limits['articles'])
                : [],
            cocktails: in_array('cocktails', $enabledTypes)
                ? $this->searchCocktails($query, $limits['cocktails'])
                : [],
        );

        // Log search for analytics (only for full searches, not limited header searches)
        if ($this->analytics && ($options['log'] ?? true)) {
            try {
                $this->analytics->logSearch($query, $result->totalCount());
            } catch (Throwable $e) {
                // Silently fail - don't break search if analytics fails
            }
        }

        return $result;
    }

    /**
     * Search products only
     */
    public function searchProducts(string $query, int $limit = 12): array
    {
        if (empty(trim($query))) {
            return [];
        }

        return $this->executeProductSearch($query, $limit);
    }

    /**
     * Search categories only
     */
    public function searchCategories(string $query, int $limit = 5): array
    {
        if (empty(trim($query))) {
            return [];
        }

        return $this->executeTaxonomySearch($query, 'categories', $limit, CategoryResult::class);
    }

    /**
     * Search tags only
     */
    public function searchTags(string $query, int $limit = 5): array
    {
        if (empty(trim($query))) {
            return [];
        }

        return $this->executeTaxonomySearch($query, 'tags', $limit, TagResult::class);
    }

    /**
     * Search brands only
     */
    public function searchBrands(string $query, int $limit = 5): array
    {
        if (empty(trim($query))) {
            return [];
        }

        return $this->executeBrandSearch($query, $limit);
    }

    /**
     * Search articles only
     */
    public function searchArticles(string $query, int $limit = 5): array
    {
        if (empty(trim($query))) {
            return [];
        }

        return $this->executePostTypeSearch($query, 'articles', $limit, ArticleResult::class);
    }

    /**
     * Search cocktails only
     */
    public function searchCocktails(string $query, int $limit = 5): array
    {
        if (empty(trim($query))) {
            return [];
        }

        return $this->executePostTypeSearch($query, 'cocktails', $limit, CocktailResult::class);
    }

    protected function executeProductSearch(string $query, int $limit): array
    {
        try {
            $this->tnt->selectIndex($this->config->getIndexFile('products'));
            $this->configureFuzzySearch();

            // Get more results for re-ranking
            $results = $this->searchWithPrefix($query, $limit * 3);

            if (empty($results['ids'])) {
                return [];
            }

            global $wpdb;

            $ids = implode(',', array_map('intval', $results['ids']));

            $rows = $wpdb->get_results("
                SELECT ID, post_title
                FROM {$wpdb->posts}
                WHERE ID IN ({$ids})
            ");

            // Custom ranking: prioritize title prefix matches
            $products = array_filter(
                array_map(fn($row) => Product::find((int) $row->ID), $rows)
            );

            $products = $this->rankByTitleMatch($products, $query);

            return array_slice($products, 0, $limit);
        } catch (Throwable $e) {
            error_log('TNTSearch Product Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Re-rank products to prioritize title matches
     */
    protected function rankByTitleMatch(array $products, string $query): array
    {
        $query = mb_strtolower(trim($query));
        $queryWords = preg_split('/\s+/', $query);

        usort($products, function ($a, $b) use ($query, $queryWords) {
            $titleA = mb_strtolower($a->title);
            $titleB = mb_strtolower($b->title);

            // Score based on how well the title matches the query
            $scoreA = 0;
            $scoreB = 0;

            // Exact title prefix match (highest priority)
            if (str_starts_with($titleA, $query)) {
                $scoreA += 1000;
            }
            if (str_starts_with($titleB, $query)) {
                $scoreB += 1000;
            }

            // Count how many query words appear in title
            foreach ($queryWords as $word) {
                if (stripos($titleA, $word) !== false) {
                    $scoreA += 100;
                }
                if (stripos($titleB, $word) !== false) {
                    $scoreB += 100;
                }

                // Bonus for word appearing at start of title
                if (str_starts_with($titleA, $word)) {
                    $scoreA += 50;
                }
                if (str_starts_with($titleB, $word)) {
                    $scoreB += 50;
                }
            }

            return $scoreB <=> $scoreA; // Higher score first
        });

        return $products;
    }

    protected function executeTaxonomySearch(string $query, string $indexName, int $limit, string $modelClass): array
    {
        try {
            $this->tnt->selectIndex($this->config->getIndexFile($indexName));
            $this->configureFuzzySearch();

            $results = $this->searchWithPrefix($query, $limit);

            if (empty($results['ids'])) {
                return [];
            }

            return array_filter(
                array_map(fn($id) => $modelClass::find((int) $id), $results['ids'])
            );
        } catch (Throwable $e) {
            error_log("TNTSearch Taxonomy Error ($indexName): " . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    protected function executeBrandSearch(string $query, int $limit): array
    {
        try {
            $this->tnt->selectIndex($this->config->getIndexFile('brands'));
            $this->configureFuzzySearch();

            $results = $this->searchWithPrefix($query, $limit);

            if (empty($results['ids'])) {
                return [];
            }

            return array_filter(
                array_map(fn($id) => BrandResult::find((int) $id), $results['ids'])
            );
        } catch (\Throwable $e) {
            error_log('TNTSearch Brand Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    protected function executePostTypeSearch(string $query, string $indexName, int $limit, string $modelClass): array
    {
        try {
            $this->tnt->selectIndex($this->config->getIndexFile($indexName));
            $this->configureFuzzySearch();

            $results = $this->searchWithPrefix($query, $limit);

            if (empty($results['ids'])) {
                return [];
            }

            return array_filter(
                array_map(fn($id) => $modelClass::find((int) $id), $results['ids'])
            );
        } catch (Throwable $e) {
            error_log("TNTSearch PostType Error ($indexName): " . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    protected function configureFuzzySearch(): void
    {
        if ($this->config->fuzzyEnabled()) {
            $this->tnt->fuzziness = true;
            $this->tnt->fuzzy_prefix_length = $this->config->fuzzyPrefixLength();
            $this->tnt->fuzzy_max_expansions = $this->config->fuzzyMaxExpansions();
            $this->tnt->fuzzy_distance = $this->config->fuzzyDistance();
        }
    }

    /**
     * Prepare query for prefix matching
     * Adds wildcards to words that are at least 3 characters
     */
    protected function prepareQueryWithPrefix(string $query): string
    {
        $words = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);
        $prepared = [];

        foreach ($words as $word) {
            // Add wildcard for prefix matching if word is 3+ chars
            if (mb_strlen($word) >= 3) {
                $prepared[] = $word . '*';
            } else {
                $prepared[] = $word;
            }
        }

        return implode(' ', $prepared);
    }

    /**
     * Search - prefix matching is handled by PrefixTokenizer during indexing
     * Runs separate searches for each synonym variant and merges results
     */
    protected function searchWithPrefix(string $query, int $limit): array
    {
        $variants = $this->getQueryVariants($query);

        $allIds = [];

        foreach ($variants as $variant) {
            $results = $this->tnt->search($variant, $limit);

            foreach ($results['ids'] ?? [] as $id) {
                $allIds[$id] = true;
            }
        }

        return ['ids' => array_keys($allIds)];
    }

    /**
     * Generate query variants by substituting synonyms
     * Handles both single-word and multi-word phrase synonyms
     *
     * e.g. "absolut vanille" with [vanille, vanilia] → ["absolut vanille", "absolut vanilia"]
     * e.g. "absolut 100cl" with [100cl, 1 liter] → ["absolut 100cl", "absolut 1 liter"]
     */
    protected function getQueryVariants(string $query): array
    {
        $handler = SynonymsHandler::fromConfig();

        if (! $handler->hasSynonyms()) {
            return [$query];
        }

        $query = mb_strtolower(trim($query));
        $variants = [$query];

        // Check multi-word phrase synonyms first (replace phrases in the full query string)
        foreach ($handler->getMultiWordLookup() as $phrase => $synonyms) {
            if (str_contains($query, $phrase)) {
                foreach ($synonyms as $synonym) {
                    if ($synonym !== $phrase) {
                        $variants[] = str_replace($phrase, $synonym, $query);
                    }
                }
            }
        }

        // Check single-word synonyms
        $words = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $i => $word) {
            $synonyms = $handler->getSynonyms($word);

            if (empty($synonyms)) {
                continue;
            }

            foreach ($synonyms as $synonym) {
                if ($synonym === $word) {
                    continue;
                }

                // Multi-word synonym replaces a single word (e.g. "100cl" → "1 liter")
                $variant = $words;
                $variant[$i] = $synonym;
                $variants[] = implode(' ', $variant);
            }
        }

        return array_unique($variants);
    }
}
