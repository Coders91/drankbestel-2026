<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithSearch;
use Livewire\Attributes\Url;
use Livewire\Component;

class SiteSearch extends Component
{
    use WithSearch;

    #[Url(as: 'q')]
    public string $query = '';

    protected function getSearchOptions(): array
    {
        return [
            'limits' => [
                'products' => 24,
                'categories' => 8,
                'tags' => 8,
                'brands' => 8,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.site-search', [
            'searchResults' => $this->getSearchResults($this->getSearchOptions()),
        ]);
    }
}
