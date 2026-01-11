<?php

namespace App\Services\Search;

class SearchConfig
{
    public function storagePath(): string
    {
        return config('search.storage_path', WP_CONTENT_DIR . '/tntsearch');
    }

    public function fuzzyEnabled(): bool
    {
        return config('search.fuzzy.enabled', true);
    }

    public function fuzzyPrefixLength(): int
    {
        return config('search.fuzzy.prefix_length', 2);
    }

    public function fuzzyMaxExpansions(): int
    {
        return config('search.fuzzy.max_expansions', 50);
    }

    public function fuzzyDistance(): int
    {
        return config('search.fuzzy.distance', 2);
    }

    public function minQueryLength(): int
    {
        return config('search.min_query_length', 2);
    }

    public function getLimit(string $type): int
    {
        return config("search.limits.{$type}", 12);
    }

    public function getIndexFile(string $type): string
    {
        return config("search.indexes.{$type}.file", "{$type}.index");
    }

    public function getTokenizer(): ?string
    {
        return config('search.tokenizer');
    }

    public function getBrandTaxonomies(): array
    {
        return config('search.indexes.brands.taxonomies', ['product_brand', 'pa_brand']);
    }
}
