<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSearch;
use App\Services\Search\SearchAnalyticsService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class HeaderSearch extends Component
{
    use WithSearch;

    public bool $showDropdown = false;
    public bool $showMobileOverlay = false;

    public function updatedQuery(): void
    {
        $this->showDropdown = true;
    }

    public function focusInput(): void
    {
        $this->showDropdown = true;
    }

    public function closeDropdown(): void
    {
        $this->showDropdown = false;
    }

    public function closeMobileSearch(): void
    {
        $this->showMobileOverlay = false;
        $this->query = '';
        $this->dispatch('mobile-search-closed');
    }

    public function selectSuggestion(string $suggestion): void
    {
        $this->query = $suggestion;
        $this->showDropdown = true;
    }

    protected function getSearchOptions(): array
    {
        return [
            'types' => ['products', 'categories', 'brands'],
            'limits' => [
                'products' => 5,
                'categories' => 3,
                'brands' => 3,
            ],
            'log' => false,
        ];
    }

    protected function getPopularSearches(): array
    {
        return Cache::remember('popular_searches', 3600, function () {
            $analytics = app(SearchAnalyticsService::class);

            if ($analytics) {
                $popular = $analytics->getPopularSearches(8, '30 days');
                // Only show searches that had results
                return collect($popular)
                    ->filter(fn($s) => $s->avg_results > 0)
                    ->take(6)
                    ->pluck('query')
                    ->toArray();
            }

            // Fallback suggestions if no analytics data
            return [
                'whisky',
                'vodka',
                'rum',
                'gin',
                'wijn',
                'bier',
            ];
        });
    }

    public function goToSearch()
    {
        $this->showMobileOverlay = false;
        return redirect()->route('search', ['q' => $this->query]);
    }

    public function render()
    {
        $hasQuery = strlen($this->query) >= 2;
        $isOpen = $this->showDropdown || $this->showMobileOverlay;

        return view('livewire.header-search', [
            'searchResults' => $isOpen && $hasQuery
                ? $this->getSearchResults($this->getSearchOptions())
                : null,
            'popularSearches' => !$hasQuery
                ? $this->getPopularSearches()
                : [],
        ]);
    }
}
