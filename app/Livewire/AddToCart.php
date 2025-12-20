<?php

namespace App\Livewire;

use App\View\Models\Product;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddToCart extends Component
{
    #[Locked]
    public int $productId;

    public int $quantity = 1;

    public bool $adding = false;

    public function mount(int $productId, int $quantity = 1): void
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function add(): void
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }

        $this->adding = true;

        $product = wc_get_product($this->productId);

        if (! $product || ! $product->is_purchasable() || ! $product->is_in_stock()) {
            $this->adding = false;

            return;
        }

        $cartItemKey = WC()->cart->add_to_cart($this->productId, $this->quantity);

        if ($cartItemKey) {
            $this->dispatch('product-added-to-cart', [
                'productId' => $this->productId,
                'name' => $product->get_name(),
                'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                'quantity' => $this->quantity,
                'cartItemKey' => $cartItemKey,
            ]);

            $this->dispatch('cart-updated');
        }

        $this->adding = false;
    }

    public function render()
    {
        return view('livewire.add-to-cart');
    }
}
