<?php

namespace App\Services\MyParcel;

use Illuminate\Support\Facades\Log;
use WC_Order;

class WebhookHandler
{
    /**
     * Shipment statuses from the MyParcel SDK.
     *
     * 1  = pending - concept
     * 2  = pending - registered (label printed)
     * 3  = enroute - handed to carrier
     * 4  = enroute - sorting
     * 5  = enroute - distribution
     * 6  = enroute - customs
     * 7  = delivered - at recipient
     * 8  = delivered - ready for pickup
     * 9  = delivered - package picked up
     * 10 = delivered - return shipment ready for pickup
     * 11 = delivered - return shipment package picked up
     * 12 = printed - letter
     * 13 = credit
     * 14 = printed - digital stamp
     * 30–38 = inactive variants
     * 99 = unknown
     */
    protected const PRINTED_STATUSES = [2, 12, 14];

    protected const HANDED_TO_CARRIER = 3;

    protected const DELIVERED_STATUSES = [7, 8, 9];

    public function handle(array $data): void
    {
        $hooks = $data['data']['hooks'] ?? [];

        foreach ($hooks as $hook) {
            $this->processHook($hook);
        }
    }

    protected function processHook(array $hook): void
    {
        $shipmentId = $hook['shipment_id'] ?? null;
        $barcode = $hook['barcode'] ?? null;
        $status = $hook['status'] ?? null;

        if (! $shipmentId) {
            Log::warning('MyParcel webhook: missing shipment_id', $hook);

            return;
        }

        $order = $this->findOrderByShipmentId($shipmentId);

        if (! $order) {
            Log::warning('MyParcel webhook: order not found', [
                'shipment_id' => $shipmentId,
            ]);

            return;
        }

        if ($status) {
            $order->update_meta_data('_myparcel_shipment_status', $status);
        }

        // Update barcode when the label has been printed
        if ($barcode && in_array($status, self::PRINTED_STATUSES, true)) {
            $order->update_meta_data('_myparcel_barcode', $barcode);
            $order->add_order_note(
                sprintf(__('MyParcel label geprint — Track & trace: %s', 'sage'), $barcode)
            );
        }

        // Carrier has scanned/collected the package
        if ($status === self::HANDED_TO_CARRIER) {
            $order->add_order_note(__('Zending is opgehaald door de bezorger', 'sage'));
        }

        // Package delivered
        if (in_array($status, self::DELIVERED_STATUSES, true)) {
            $order->add_order_note(__('Zending is bezorgd', 'sage'));
        }

        $order->save();

        do_action('myparcel_shipment_status_changed', $order, $status, $barcode);

        Log::info('MyParcel webhook processed', [
            'order_id' => $order->get_id(),
            'shipment_id' => $shipmentId,
            'status' => $status,
            'barcode' => $barcode,
        ]);
    }

    protected function findOrderByShipmentId(int $shipmentId): ?WC_Order
    {
        $orders = wc_get_orders([
            'meta_key' => '_myparcel_shipment_id',
            'meta_value' => $shipmentId,
            'limit' => 1,
        ]);

        return $orders[0] ?? null;
    }
}
