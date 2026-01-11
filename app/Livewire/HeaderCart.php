<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class HeaderCart extends Component
{
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        unset($this->itemCount);
    }

    #[Computed]
    public function itemCount(): int
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return 0;
        }

        return WC()->cart->get_cart_contents_count();
    }

    public function render()
    {
        return view('livewire.header-cart');
    }
}
