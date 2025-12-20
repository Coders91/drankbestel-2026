<?php

namespace App\View\Components;

use App\View\Models\CartItem;
use App\View\Models\CartTotals;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CheckoutOrderReview extends Component
{
    public array $items;

    public CartTotals $totals;

    public function __construct(
        public bool $newsletter = false,
        public bool $ageCheck = false,
    ) {
        $this->items = $this->getItems();
        $this->totals = CartTotals::fromCart();
    }

    /**
     * Get cart items as typed objects
     *
     * @return array<CartItem>
     */
    protected function getItems(): array
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return [];
        }

        $items = [];

        foreach (WC()->cart->get_cart() as $cartItemKey => $cartItem) {
            $items[] = CartItem::fromCartItem($cartItem, $cartItemKey);
        }

        return $items;
    }

    public function render(): View
    {
        return view('components.checkout-order-review');
    }
}
