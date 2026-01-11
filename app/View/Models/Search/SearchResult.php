<?php

namespace App\View\Models\Search;

use App\View\Models\Product;

readonly class SearchResult
{
    /**
     * @param Product[] $products
     * @param CategoryResult[] $categories
     * @param TagResult[] $tags
     * @param BrandResult[] $brands
     * @param ArticleResult[] $articles
     * @param CocktailResult[] $cocktails
     */
    public function __construct(
        public string $query,
        public array $products = [],
        public array $categories = [],
        public array $tags = [],
        public array $brands = [],
        public array $articles = [],
        public array $cocktails = [],
    ) {}

    public function isEmpty(): bool
    {
        return empty($this->products)
            && empty($this->categories)
            && empty($this->tags)
            && empty($this->brands)
            && empty($this->articles)
            && empty($this->cocktails);
    }

    public function totalCount(): int
    {
        return count($this->products)
            + count($this->categories)
            + count($this->tags)
            + count($this->brands)
            + count($this->articles)
            + count($this->cocktails);
    }

    public function hasProducts(): bool
    {
        return !empty($this->products);
    }

    public function hasCategories(): bool
    {
        return !empty($this->categories);
    }

    public function hasTags(): bool
    {
        return !empty($this->tags);
    }

    public function hasBrands(): bool
    {
        return !empty($this->brands);
    }

    public function hasArticles(): bool
    {
        return !empty($this->articles);
    }

    public function hasCocktails(): bool
    {
        return !empty($this->cocktails);
    }
}
