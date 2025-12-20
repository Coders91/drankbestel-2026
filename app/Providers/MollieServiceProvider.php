<?php

namespace App\Providers;

use App\Woocommerce\Gateways\IdealGateway;
use App\Woocommerce\Gateways\CreditcardGateway;
use App\Woocommerce\Gateways\PaypalGateway;
use App\Services\MollieService;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Mollie\Api\MollieApiClient;

use WP_REST_Request;
use WP_REST_Response;
use WC_Order;

class MollieServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(MollieApiClient::class, function ($app) {
            $client = new MollieApiClient();
            $client->setApiKey(config('services.mollie.api_key'));

            return $client;
        });

        $this->app->alias(MollieApiClient::class, 'mollie');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        add_filter('woocommerce_payment_gateways', function ($gateways) {
            $gateways[] = IdealGateway::class;
            $gateways[] = CreditcardGateway::class;
            $gateways[] = PaypalGateway::class;
            return $gateways;
        });

        // Register Mollie webhook REST API endpoint
        add_action('rest_api_init', function () {
            register_rest_route('mollie/v1', '/webhook', [
                'methods' => 'POST',
                'callback' => [$this, 'handleWebhook'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    /**
     * Handle Mollie webhook callback
     */
    public function handleWebhook(WP_REST_Request $request): WP_REST_Response
    {
        $paymentId = $request->get_param('id');

        if (! $paymentId) {
            Log::warning('Mollie webhook received without payment ID');
            return new WP_REST_Response(null, 200);
        }

        try {
            $mollieService = app(MollieService::class);
            $payment = $mollieService->getPayment($paymentId);
        } catch (\Exception $e) {
            Log::error('Mollie webhook error: ' . $e->getMessage());
            return new WP_REST_Response(null, 200);
        }

        if (! $payment) {
            Log::error('Mollie webhook: Payment not found', ['payment_id' => $paymentId]);
            return new WP_REST_Response(null, 200);
        }

        $orderId = $payment->metadata->order_id ?? null;

        if (! $orderId) {
            Log::error('Mollie webhook: No order ID in payment metadata', ['payment_id' => $paymentId]);
            return new WP_REST_Response(null, 200);
        }

        $order = wc_get_order($orderId);

        if (! $order) {
            Log::error('Mollie webhook: Order not found', ['order_id' => $orderId]);
            return new WP_REST_Response(null, 200);
        }

        // Store the Mollie payment ID on the order
        $order->update_meta_data('_mollie_payment_id', $paymentId);

        // Handle payment status
        if ($payment->isPaid()) {
            $this->handlePaidPayment($order, $payment);
        } elseif ($payment->isFailed()) {
            $this->handleFailedPayment($order, $payment);
        } elseif ($payment->isCanceled()) {
            $this->handleCanceledPayment($order, $payment);
        } elseif ($payment->isExpired()) {
            $this->handleExpiredPayment($order, $payment);
        } elseif ($payment->isPending()) {
            $this->handlePendingPayment($order, $payment);
        }

        $order->save();

        return new WP_REST_Response(null, 200);
    }

    protected function handlePaidPayment(WC_Order $order, $payment): void
    {
        if (! $order->is_paid()) {
            $order->payment_complete($payment->id);
            $order->add_order_note(
                sprintf(__('Mollie payment completed (Payment ID: %s)', 'sage'), $payment->id)
            );
        }
    }

    protected function handleFailedPayment(WC_Order $order, $payment): void
    {
        if ($order->get_status() !== 'failed') {
            $order->update_status('failed', sprintf(
                __('Mollie payment failed (Payment ID: %s)', 'sage'),
                $payment->id
            ));
        }
    }

    protected function handleCanceledPayment(WC_Order $order, $payment): void
    {
        if ($order->get_status() !== 'cancelled') {
            $order->update_status('cancelled', sprintf(
                __('Mollie payment was cancelled by the customer (Payment ID: %s)', 'sage'),
                $payment->id
            ));
        }
    }

    protected function handleExpiredPayment(WC_Order $order, $payment): void
    {
        if ($order->get_status() !== 'failed') {
            $order->update_status('failed', sprintf(
                __('Mollie payment expired (Payment ID: %s)', 'sage'),
                $payment->id
            ));
        }
    }

    protected function handlePendingPayment(WC_Order $order, $payment): void
    {
        if (! in_array($order->get_status(), ['processing', 'completed', 'on-hold'])) {
            $order->update_status('on-hold', sprintf(
                __('Mollie payment is pending (Payment ID: %s)', 'sage'),
                $payment->id
            ));
        }
    }
}
