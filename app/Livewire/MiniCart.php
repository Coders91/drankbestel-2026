<?php

namespace App\Livewire;

use App\View\Models\Product;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MiniCart extends Component
{
    public bool $showToast = false;

    public ?Product $lastAddedProduct = null;

    #[On('product-added-to-cart')]
    public function onProductAdded(array $data): void
    {
        $this->lastAddedProduct = Product::find($data['productId']);
        $this->showToast = true;
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        unset($this->itemCount);
        unset($this->total);
    }

    public function hideToast(): void
    {
        $this->showToast = false;
    }

    public function goToCart(): mixed
    {
        $this->showToast = false;

        return $this->redirect(route('cart'));
    }

    #[Computed]
    public function itemCount(): int
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return 0;
        }

        return WC()->cart->get_cart_contents_count();
    }

    #[Computed]
    public function total(): string
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return '';
        }

        return WC()->cart->get_cart_total();
    }

    public function render()
    {
        return view('livewire.mini-cart');
    }
}
