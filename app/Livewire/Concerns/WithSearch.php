<?php

namespace App\Livewire\Concerns;

use App\Services\Search\TNTSearchService;
use App\View\Models\Search\SearchResult;

trait WithSearch
{
    public string $query = '';

    protected function getSearchService(): TNTSearchService
    {
        return app(TNTSearchService::class);
    }

    protected function getSearchResults(array $options = []): ?SearchResult
    {
        $minLength = config('search.min_query_length', 2);

        if (strlen($this->query) < $minLength) {
            return null;
        }

        return $this->getSearchService()->search($this->query, $options);
    }

    protected function clearSearch(): void
    {
        $this->query = '';
    }
}
