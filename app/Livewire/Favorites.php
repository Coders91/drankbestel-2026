<?php

namespace App\Livewire;

use App\View\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Favorites extends Component
{
    #[Locked]
    public array $favoriteIds = [];

    /**
     * Load favorites from client-side localStorage
     */
    public function loadFavorites(array $ids): void
    {
        $this->favoriteIds = array_map('intval', $ids);
    }

    /**
     * Remove a product from favorites
     */
    public function removeFromFavorites(int $productId): void
    {
        $this->favoriteIds = array_filter(
            $this->favoriteIds,
            fn ($id) => $id !== $productId
        );

        $this->dispatch('remove-favorite', productId: $productId);
    }

    /**
     * Get favorite products
     *
     * @return array<Product>
     */
    #[Computed]
    public function products(): array
    {
        if (empty($this->favoriteIds)) {
            return [];
        }

        $products = [];

        foreach ($this->favoriteIds as $productId) {
            $product = Product::find($productId);

            if ($product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Check if favorites list is empty
     */
    #[Computed]
    public function isEmpty(): bool
    {
        return empty($this->favoriteIds);
    }

    public function render()
    {
        return view('livewire.favorites')
        ->layoutData(['breadcrumbs' => false]);
    }
}
