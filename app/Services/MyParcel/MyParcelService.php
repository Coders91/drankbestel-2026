<?php

namespace App\Services\MyParcel;

use Illuminate\Support\Facades\Log;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use WC_Order;

class MyParcelService
{
    public function __construct(
        protected ShipmentBuilder $builder,
    ) {}

    public function createShipment(WC_Order $order): int
    {
        $consignment = $this->builder->build($order);

        $collection = (new MyParcelCollection)
            ->addConsignment($consignment)
            ->createConcepts()
            ->setPdfOfLabels();

        $shipment = $collection->getOneConsignment();
        $shipmentId = $shipment->getConsignmentId();
        $barcode = $shipment->getBarcode();

        $order->update_meta_data('_myparcel_shipment_id', $shipmentId);

        if ($barcode) {
            $order->update_meta_data('_myparcel_barcode', $barcode);
        }

        $note = sprintf(__('MyParcel zending aangemaakt (ID: %d)', 'sage'), $shipmentId);
        if ($barcode) {
            $note .= sprintf(' — Track & trace: %s', $barcode);
        }
        $order->add_order_note($note);
        $order->save();

        Log::info('MyParcel shipment created', [
            'order_id' => $order->get_id(),
            'shipment_id' => $shipmentId,
            'barcode' => $barcode,
        ]);

        return $shipmentId;
    }

    /**
     * @param  WC_Order[]  $orders
     * @return array<int, int> [order_id => shipment_id]
     */
    public function createShipments(array $orders): array
    {
        $collection = new MyParcelCollection;

        foreach ($orders as $order) {
            if (! $this->canExport($order) || $this->isExported($order)) {
                continue;
            }

            $collection->addConsignment($this->builder->build($order));
        }

        if (! $collection->count()) {
            return [];
        }

        $collection
            ->createConcepts()
            ->setPdfOfLabels();

        $results = [];

        foreach ($collection->getConsignments() as $consignment) {
            $orderId = (int) $consignment->getReferenceIdentifier();
            $shipmentId = $consignment->getConsignmentId();
            $barcode = $consignment->getBarcode();
            $order = wc_get_order($orderId);

            if (! $order) {
                continue;
            }

            $order->update_meta_data('_myparcel_shipment_id', $shipmentId);

            if ($barcode) {
                $order->update_meta_data('_myparcel_barcode', $barcode);
            }

            $note = sprintf(__('MyParcel zending aangemaakt (ID: %d)', 'sage'), $shipmentId);
            if ($barcode) {
                $note .= sprintf(' — Track & trace: %s', $barcode);
            }
            $order->add_order_note($note);
            $order->save();

            $results[$orderId] = $shipmentId;
        }

        Log::info('MyParcel bulk shipments created', ['results' => $results]);

        return $results;
    }

    public function getTrackingUrl(WC_Order $order): ?string
    {
        $barcode = $order->get_meta('_myparcel_barcode');

        if (! $barcode) {
            return null;
        }

        $postalCode = $order->get_shipping_postcode() ?: $order->get_billing_postcode();

        return sprintf(
            'https://myparcel.me/track-trace/%s/%s/%s',
            $barcode,
            $postalCode,
            $order->get_shipping_country() ?: $order->get_billing_country() ?: 'NL'
        );
    }

    public function canExport(WC_Order $order): bool
    {
        $exportable = config('myparcel.exportable_statuses', ['processing', 'on-hold']);

        return in_array($order->get_status(), $exportable, true);
    }

    public function isExported(WC_Order $order): bool
    {
        return (bool) $order->get_meta('_myparcel_shipment_id');
    }
}
