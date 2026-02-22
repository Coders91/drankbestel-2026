<?php

namespace App\Livewire;

use App\View\Models\Product;
use Livewire\Component;
use WP_Query;

class ProductLoadMore extends Component
{
    public int $perPage = 18;
    public int $page = 1;
    public int $initialCount = 0;
    public int $totalProducts = 0;
    public int $maxPages = 1;
    public array $queryVars = [];
    public array $additionalProductIds = [];

    public function mount(array $queryVars = [], int $maxPages = 1, int $initialCount = 0, int $totalProducts = 0): void
    {
        $this->queryVars = $queryVars;
        $this->maxPages = $maxPages;
        $this->initialCount = $initialCount;
        $this->totalProducts = $totalProducts;
        $this->page = get_query_var('paged') ?: 1;
    }

    public function loadMore(): void
    {
        if ($this->page >= $this->maxPages) {
            return;
        }

        $this->page++;

        $this->loadPage($this->page);

        $this->dispatch('page-updated', page: $this->page);
    }

    public function loadUpToPage(int $targetPage): void
    {
        $targetPage = min($targetPage, $this->maxPages);

        while ($this->page < $targetPage) {
            $this->page++;
            $this->loadPage($this->page);
        }
    }

    private function loadPage(int $page): void
    {
        $args = array_merge($this->queryVars, [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $this->perPage,
            'paged' => $page,
            'fields' => 'ids',
        ]);

        $query = new WP_Query($args);

        $this->additionalProductIds = array_merge($this->additionalProductIds, $query->posts);
        $this->maxPages = $query->max_num_pages;
    }

    public function getShownCountProperty(): int
    {
        return $this->initialCount + count($this->additionalProductIds);
    }

    public function getAdditionalProductsProperty(): array
    {
        if (empty($this->additionalProductIds)) {
            return [];
        }

        Product::primeCache($this->additionalProductIds);

        return collect($this->additionalProductIds)
            ->map(fn (int $id) => Product::find($id))
            ->filter()
            ->values()
            ->all();
    }

    public function getHasMoreProperty(): bool
    {
        return $this->page < $this->maxPages;
    }

    public function render()
    {
        return view('livewire.product-load-more', [
            'shownCount' => $this->shownCount,
            'totalProducts' => $this->totalProducts,
            'additionalProducts' => $this->additionalProducts,
            'hasMore' => $this->hasMore,
        ]);
    }
}
