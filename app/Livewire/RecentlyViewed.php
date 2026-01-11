<?php

namespace App\Livewire;

use App\View\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RecentlyViewed extends Component
{
    #[Locked]
    public array $productIds = [];

    #[Locked]
    public ?int $excludeId = null;

    public function mount(?int $excludeId = null): void
    {
        $this->excludeId = $excludeId;
    }

    /**
     * Load recently viewed product IDs from client-side sessionStorage
     */
    public function loadProducts(array $ids): void
    {
        $this->productIds = array_map('intval', $ids);

        // Filter out excluded product
        if ($this->excludeId) {
            $this->productIds = array_filter(
                $this->productIds,
                fn ($id) => $id !== $this->excludeId
            );
        }

        // Limit to 12 products
        $this->productIds = array_slice($this->productIds, 0, 12);
    }

    /**
     * Get recently viewed products
     *
     * @return array<Product>
     */
    #[Computed]
    public function products(): array
    {
        if (empty($this->productIds)) {
            return [];
        }

        $products = [];

        foreach ($this->productIds as $productId) {
            $product = Product::find($productId);

            if ($product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Check if there are products to display
     */
    #[Computed]
    public function hasProducts(): bool
    {
        return ! empty($this->productIds);
    }

    public function render()
    {
        return view('livewire.recently-viewed');
    }
}
