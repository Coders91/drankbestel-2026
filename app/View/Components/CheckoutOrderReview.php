<?php

namespace App\View\Components;

use App\View\Models\CartItem;
use App\View\Models\CartTotals;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class CheckoutOrderReview extends Component
{
    public Collection $items;
    public Collection $visibleItems;
    public Collection $hiddenItems;

    public CartTotals $totals;

    public function __construct(
        public bool $newsletter = false,
        public bool $ageCheck = false,
    ) {
        $this->items = $this->getItems();
        $this->visibleItems = $this->items->take(3);
        $this->hiddenItems = $this->items->slice(3);
        $this->totals = CartTotals::fromCart();
    }

    /**
     * Get cart items as typed objects
     *
     * @return array<CartItem>
     */
    protected function getItems(): Collection
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return collect();
        }

        $items = collect();

        foreach (WC()->cart->get_cart() as $cartItemKey => $cartItem) {
            $items->push(CartItem::fromCartItem($cartItem, $cartItemKey));
        }

        return $items;
    }

    public function render(): View
    {
        return view('components.checkout-order-review');
    }
}
