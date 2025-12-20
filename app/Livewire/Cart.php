<?php

namespace App\Livewire;

use App\View\Models\AppliedCoupon;
use App\View\Models\CartItem;
use App\View\Models\CartTotals;
use App\View\Models\FreeShippingProgress;
use Livewire\Component;

class Cart extends Component
{
    public string $couponCode = '';

    public array $messages = [];

    /**
     * Get cart items as typed objects
     *
     * @return array<CartItem>
     */
    public function getItemsProperty(): array
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

    /**
     * Get cart totals
     */
    public function getTotalsProperty(): CartTotals
    {
        return CartTotals::fromCart();
    }

    /**
     * Get free shipping progress
     */
    public function getFreeShippingProperty(): FreeShippingProgress
    {
        return FreeShippingProgress::calculate($this->totals->subtotal->amount->decimal());
    }

    /**
     * Get applied coupons
     *
     * @return array<AppliedCoupon>
     */
    public function getCouponsProperty(): array
    {
        return AppliedCoupon::allFromCart();
    }

    /**
     * Check if cart is empty
     */
    public function getIsEmptyProperty(): bool
    {
        return ! function_exists('WC') || ! WC()->cart || WC()->cart->is_empty();
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(string $cartItemKey, int $quantity): void
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }

        $this->clearMessages();

        if ($quantity <= 0) {
            $this->removeItem($cartItemKey);

            return;
        }

        $cartItem = WC()->cart->get_cart_item($cartItemKey);

        if (! $cartItem) {
            return;
        }

        $product = $cartItem['data'];
        $maxQty = $product->get_stock_quantity() ?: 99;

        if ($quantity > $maxQty) {
            $quantity = $maxQty;
            $this->messages['quantity'] = sprintf(
                __('Maximum beschikbare hoeveelheid voor %s is %d', 'sage'),
                $product->get_name(),
                $maxQty
            );
        }

        WC()->cart->set_quantity($cartItemKey, $quantity);
        WC()->cart->calculate_totals();

        $this->dispatch('cart-updated');
    }

    /**
     * Increase item quantity by 1
     */
    public function increaseQuantity(string $cartItemKey): void
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }

        $cartItem = WC()->cart->get_cart_item($cartItemKey);

        if ($cartItem) {
            $this->updateQuantity($cartItemKey, $cartItem['quantity'] + 1);
        }
    }

    /**
     * Decrease item quantity by 1
     */
    public function decreaseQuantity(string $cartItemKey): void
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }

        $cartItem = WC()->cart->get_cart_item($cartItemKey);

        if ($cartItem) {
            $newQuantity = $cartItem['quantity'] - 1;

            if ($newQuantity <= 0) {
                $this->removeItem($cartItemKey);
            } else {
                $this->updateQuantity($cartItemKey, $newQuantity);
            }
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(string $cartItemKey): void
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }

        $this->clearMessages();

        WC()->cart->remove_cart_item($cartItemKey);
        WC()->cart->calculate_totals();

        $this->dispatch('cart-updated');
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(): void
    {
        $this->clearMessages();

        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }

        $couponCode = sanitize_text_field(trim($this->couponCode));

        if (empty($couponCode)) {
            $this->messages['coupon_error'] = __('Voer een kortingscode in.', 'sage');

            return;
        }

        // Check if already applied
        if (WC()->cart->has_discount($couponCode)) {
            $this->messages['coupon_error'] = __('Deze kortingscode is al toegepast.', 'sage');

            return;
        }

        $result = WC()->cart->apply_coupon($couponCode);

        if ($result) {
            $this->couponCode = '';
            $this->messages['coupon_success'] = __('Kortingscode toegepast!', 'sage');
        } else {
            // Get WooCommerce notice for error message
            $notices = wc_get_notices('error');
            wc_clear_notices();

            $errorMessage = ! empty($notices)
                ? strip_tags($notices[0]['notice'])
                : __('Ongeldige kortingscode.', 'sage');

            $this->messages['coupon_error'] = $errorMessage;
        }

        WC()->cart->calculate_totals();
        $this->dispatch('cart-updated');
    }

    /**
     * Remove applied coupon
     */
    public function removeCoupon(string $couponCode): void
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }

        $this->clearMessages();

        WC()->cart->remove_coupon($couponCode);
        WC()->cart->calculate_totals();

        $this->dispatch('cart-updated');
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): void
    {
        if (! function_exists('WC') || ! WC()->cart) {
            return;
        }

        WC()->cart->empty_cart();

        $this->dispatch('cart-updated');
    }

    /**
     * Navigate to checkout
     */
    public function proceedToCheckout()
    {
        return $this->redirect(route('checkout'));
    }

    /**
     * Clear all messages
     */
    private function clearMessages(): void
    {
        $this->messages = [];
    }

    public function render()
    {
        return view('livewire.cart')
            ->layoutData(['breadcrumbs' => false]);
    }
}
