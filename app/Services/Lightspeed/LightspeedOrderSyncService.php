<?php

namespace App\Services\Lightspeed;

use Exception;
use Illuminate\Support\Facades\Log;
use TimothyDC\LightspeedRetailApi\Facades\LightspeedRetailApi;
use WC_Order;

class LightspeedOrderSyncService
{
    /**
     * Sync a WooCommerce order to Lightspeed Retail
     */
    public function syncOrderToLightspeed(int $orderId): void
    {
        $order = wc_get_order($orderId);

        if (! $order) {
            Log::warning("Lightspeed sync skipped: Order #{$orderId} not found.");
            return;
        }

        // Skip sync if not in production environment
        if (config('app.env') !== 'production') {
            Log::info("Lightspeed sync skipped for Order #{$orderId}: Not in production environment.");
            $order->add_order_note('Lightspeed sync skipped (non-production environment).');
            return;
        }

        $order->add_order_note('Lightspeed sync started.');

        try {
            $saleLines = $this->generateSaleLines($order->get_items());

            // Skip if no valid sale lines (no products with Lightspeed IDs)
            if (empty($saleLines)) {
                $order->add_order_note('Lightspeed sync skipped: No products with Lightspeed IDs found.');
                Log::info("Lightspeed sync skipped for Order #{$orderId}: No products with Lightspeed IDs.");
                return;
            }

            $salePayment = $this->generateSalePayment($order);

            // Add shipping costs if applicable
            $shippingCosts = $order->get_shipping_total();
            if ($shippingCosts > 0) {
                $saleLines[] = [
                    'itemID' => (int) config('services.lightspeed.delivery_costs_item_id'),
                    'unitQuantity' => 1,
                    'unitPrice' => $shippingCosts,
                    'taxRate' => 0,
                    'saleLineType' => 1,
                ];
            }

            // Create sale in Lightspeed
            LightspeedRetailApi::api()->sale()->create([
                'shopID' => (int) config('services.lightspeed.shop_id', 1),
                'employeeID' => (int) config('services.lightspeed.employee_id', 1),
                'registerID' => (int) config('services.lightspeed.register_id', 1),
                'customerID' => (int) config('services.lightspeed.customer_id'),
                'completed' => true,
                'completeTime' => $order->get_date_paid() ? $order->get_date_paid()->format('Y-m-d\TH:i:s') : null,
                'referenceNumber' => $order->get_order_number(),
                'total' => $order->get_total(),
                'SaleLines' => [
                    'SaleLine' => $saleLines,
                ],
                'SalePayments' => [
                    'SalePayment' => $salePayment,
                ],
            ]);

            $order->add_order_note('Order met success aangemaakt in Lightspeed');
            $order->save();

            Log::info("Lightspeed sync successful for Order #{$orderId}");
        } catch (Exception $e) {
            $errorMessage = sprintf(
                'Lightspeed Sync Failed: %s in file %s on line %s.',
                $e->getMessage(),
                basename($e->getFile()),
                $e->getLine()
            );

            $order->add_order_note($errorMessage);
            $order->save();

            Log::error("Lightspeed sync error for Order #{$orderId}", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Generate sale lines from WooCommerce order items
     */
    private function generateSaleLines(array $items): array
    {
        $saleLines = [];

        if (empty($items)) {
            return $saleLines;
        }

        foreach ($items as $item) {
            $product = $item->get_product();

            if (! $product) {
                continue;
            }

            $lightspeedId = get_field('lightspeed_id', $product->get_id());

            if (! $lightspeedId) {
                continue;
            }

            $quantity = $item->get_quantity();
            $total = $item->get_total();

            $saleLines[] = [
                'itemID' => (int) $lightspeedId,
                'unitQuantity' => $quantity,
                'unitPrice' => number_format($total / $quantity, 2, '.', ''),
                'taxRate' => 0,
                'discount' => 0,
                'discountType' => 0,
                'discountAmount' => 0,
                'saleLineType' => 1,
            ];
        }

        return $saleLines;
    }

    /**
     * Generate sale payment data from WooCommerce order
     */
    private function generateSalePayment(WC_Order $order): array
    {
        return [
            'amount' => $order->get_total(),
            'PaymentType' => [
                'paymentTypeID' => (int) config('services.lightspeed.ideal_payment_id'),
                'name' => $order->get_payment_method_title(),
            ],
        ];
    }
}
