<?php

namespace App\Http\Controllers;

use App\Livewire\Checkout;
use App\View\Models\Order;
use DateTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController
{
    /**
     * Handle payment return from Mollie
     */
    public function paymentReturn(int $order_id, Request $request): View|RedirectResponse
    {
        $order_key = $request->query('key');

        if (! $order_key) {
            abort(403, 'Order key is required');
        }

        $order = wc_get_order($order_id);

        if (! $order || $order->get_order_key() !== $order_key) {
            abort(404, 'Order not found');
        }

        $status = $order->get_status();

        if (in_array($status, ['processing', 'completed', 'on-hold'])) {
            return $this->handleSuccessfulPayment($order);
        }

        if (in_array($status, ['failed', 'cancelled'])) {
            return $this->handleFailedPayment($order, $status);
        }

        return view('woocommerce.checkout.payment-pending', [
            'order_id' => $order->get_id(),
            'order_key' => $order->get_order_key(),
        ]);
    }

    /**
     * Display thank you page
     */
    public function thankYou(int $order_id, Request $request)
    {
        $order_key = $request->query('key');

        if (! $order_key) {
            abort(403, 'Order key is required');
        }

        $order = wc_get_order($order_id);

        if (! $order) {
            abort(404, 'Order not found');
        }

        if ($order->get_order_key() !== $order_key) {
            abort(404, 'Order not found');
        }

        if ($this->isThankYouPageExpired($order)) {
            abort(403, 'Access to this page has expired');
        }

        $order = Order::find($order);

        return view('woocommerce.checkout.thankyou', ['order' => $order]);
    }

    private function handleSuccessfulPayment(\WC_Order $order): RedirectResponse
    {
        WC()->cart->empty_cart();
        WC()->session->set(Checkout::PENDING_ORDER_KEY, null);

        return redirect()->route('thankyou', [
            'order_id' => $order->get_id(),
            'key' => $order->get_order_key(),
        ]);
    }

    private function handleFailedPayment(\WC_Order $order, string $status): RedirectResponse
    {
        if (WC()->cart->is_empty()) {
            foreach ($order->get_items() as $item) {
                WC()->cart->add_to_cart(
                    $item->get_product_id(),
                    $item->get_quantity(),
                    $item->get_variation_id()
                );
            }
        }

        WC()->session->set(Checkout::PENDING_ORDER_KEY, null);

        $error = $status === 'cancelled' ? 'cancelled' : 'failed';

        return redirect()->route('checkout', ['payment_error' => $error]);
    }

    /**
     * @throws \Exception
     */
    private function isThankYouPageExpired(\WC_Order $order): bool
    {
        $hasAuth = current_user_can('administrator') || current_user_can('manage_woocommerce');

        if ($hasAuth) {
            return false;
        }

        $created_at = $order->get_date_created();
        $order_time = new DateTime($created_at->date('Y-m-d H:i:s'));
        $current_time = new DateTime;

        $diff_in_minutes = ($current_time->getTimestamp() - $order_time->getTimestamp()) / 60;

        return $diff_in_minutes > 5;
    }
}
