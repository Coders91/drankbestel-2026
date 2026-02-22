<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;
use WC_Product;

class AddToCart extends Component
{
    #[Locked]
    public int $productId;

    public int $quantity = 1;
    public bool $adding = false;

    public bool $soldAsPack = false;
    public int $packSize = 1;

    public bool $disabled = false;

    public bool $isSingleProduct = false;

    public ?string $errorMessage = null;

    public function mount(int $productId, int $quantity = 1, bool $isSingleProduct = false): void
    {
        $this->productId = $productId;
        $this->quantity  = max(1, $quantity);
        $this->isSingleProduct = $isSingleProduct;

        $this->initializePackSettings();
    }

    public function addPack(int $multiplier = 1): void
    {
        $this->add($this->packSize * max(1, $multiplier));
    }

    public function add(?int $qty = null): void
    {
        $this->resetError();
        $this->adding = true;

        if (! $this->ensureWooIsReady()) {
            return;
        }

        $product = $this->getProduct();

        if (! $product || ! $this->validateProduct($product)) {
            return;
        }

        $quantity = max(1, $qty ?? $this->quantity);

        if (! $this->validateStock($product, $quantity)) {
            return;
        }

        $cartItemKey = WC()->cart->add_to_cart($this->productId, $quantity);

        if (! $cartItemKey) {
            $this->dispatchError($this->getWooCommerceError());
            return;
        }

        $this->dispatchSuccess($product, $quantity, $cartItemKey);
        $this->adding = false;
    }

    protected function getProduct(): ?WC_Product
    {
        return wc_get_product($this->productId) ?: null;
    }

    protected function initializePackSettings(): void
    {
        $this->soldAsPack = (bool) get_field('product_sold_as_pack', $this->productId);
        $this->packSize   = $this->soldAsPack
            ? (int) get_field('product_pack_size', $this->productId)
            : 1;
    }

    protected function ensureWooIsReady(): bool
    {
        if (! function_exists('WC') || ! WC()->cart) {
            Log::error('AddToCart: WooCommerce not available');
            $this->dispatchError(__('Er is een fout opgetreden. Probeer het opnieuw.', 'sage'));

            return false;
        }

        if (WC()->session && ! WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

        return true;
    }

    protected function validateProduct(WC_Product $product): bool
    {
        if (! $product->is_purchasable()) {
            $this->dispatchError(__('Dit product kan niet worden besteld.', 'sage'));
            return false;
        }

        if (! $product->is_in_stock()) {
            $this->dispatchError(__('Dit product is niet op voorraad.', 'sage'));
            return false;
        }

        return true;
    }

    protected function validateStock(WC_Product $product, int $quantity): bool
    {
        if (! $product->managing_stock()) {
            return true;
        }

        $stock = $product->get_stock_quantity();
        $inCart = $this->getCartQuantityForProduct($this->productId);

        if ($stock !== null && ($inCart + $quantity) > $stock) {
            $available = max(0, $stock - $inCart);

            $this->dispatchError(
                $available > 0
                    ? sprintf(__('Er zijn nog maar %d stuks beschikbaar.', 'sage'), $available)
                    : __('Je hebt de maximale voorraad al in je winkelwagen.', 'sage')
            );

            return false;
        }

        return true;
    }

    protected function dispatchSuccess(
        WC_Product $product,
        int $quantity,
        string $cartItemKey
    ): void {
        $this->dispatch('product-added-to-cart', [
            'productId'    => $this->productId,
            'name'         => $product->get_name(),
            'image'        => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
            'quantity'     => $quantity,
            'cartItemKey'  => $cartItemKey,
        ]);

        $this->dispatch('cart-updated');
    }

    protected function resetError(): void
    {
        $this->errorMessage = null;
    }

    protected function dispatchError(string $message): void
    {
        $this->errorMessage = $message;
        $this->adding = false;

        $this->dispatch('add-to-cart-error', [
            'productId' => $this->productId,
            'message'   => $message,
        ]);
    }

    protected function getCartQuantityForProduct(int $productId): int
    {
        if (! WC()->cart) {
            return 0;
        }

        return collect(WC()->cart->get_cart())
            ->where('product_id', $productId)
            ->sum('quantity');
    }

    protected function getWooCommerceError(): string
    {
        $notices = wc_get_notices('error');

        if (! empty($notices)) {
            $notice = $notices[0];
            wc_clear_notices();

            return wp_strip_all_tags(
                is_array($notice) ? ($notice['notice'] ?? '') : $notice
            );
        }

        return __('Kan product niet toevoegen aan winkelwagen.', 'sage');
    }

    public function render()
    {
        return view('livewire.add-to-cart');
    }
}
