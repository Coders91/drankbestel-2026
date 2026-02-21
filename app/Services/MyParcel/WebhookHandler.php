<?php

namespace App\Services\MyParcel;

use Illuminate\Support\Facades\Log;
use WC_Order;

class WebhookHandler
{
    protected const STATUS_MAP = [
        1 => 'Concept',
        2 => 'Geregistreerd',
        3 => 'Aangemeld bij bezorger',
        4 => 'Sortering',
        5 => 'Bezorging',
        6 => 'Bezorgd',
        7 => 'Niet bezorgd',
        8 => 'Klaar om op te halen',
        9 => 'Opgehaald',
        10 => 'Afgehandeld',
        11 => 'Creditering',
        12 => 'Inactief',
        30 => 'Niet bij PostNL',
        31 => 'Retour bij PostNL',
        32 => 'Retour geleverd',
        33 => 'Brief',
        34 => 'Geannuleerd',
        35 => 'Geweigerd',
        36 => 'Uitgesteld',
        37 => 'Klantgegevens niet compleet',
        38 => 'Onbekend',
    ];

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

        $statusName = self::STATUS_MAP[$status] ?? "Onbekend ({$status})";

        if ($barcode) {
            $order->update_meta_data('_myparcel_barcode', $barcode);
        }

        $order->update_meta_data('_myparcel_shipment_status', $statusName);

        $note = sprintf(__('MyParcel status: %s', 'sage'), $statusName);
        if ($barcode) {
            $note .= sprintf(' (Track & trace: %s)', $barcode);
        }
        $order->add_order_note($note);

        $order->save();

        do_action('myparcel_shipment_status_changed', $order, $statusName, $barcode);

        Log::info('MyParcel webhook processed', [
            'order_id' => $order->get_id(),
            'shipment_id' => $shipmentId,
            'status' => $statusName,
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
