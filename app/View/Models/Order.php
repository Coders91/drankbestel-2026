<?php

namespace App\View\Models;

use Livewire\Wireable;

use App\Support\Money;

use WC_Order;
use WC_Order_Item_Product;

readonly class Order implements Wireable
{
    public function __construct(
        public int          $id,
        public string       $key,
        public string       $number,
        public string       $status,
        public Price        $total,
        public Price        $subtotal,
        public string       $total_quantity,
        public string       $payment_method,
        public string       $payment_method_title,
        public string       $shipping_address,
        public string       $shipping_postcode,
        public string       $shipping_city,
        public string       $shipping_method,
        public Price        $shipping_total,
        public bool         $has_free_shipping,
        public string       $shipping_carrier,
        public string       $expected_delivery_date,
        public string       $customer_email,
        public ?string      $customer_company,
        public array        $items,
    ) {}

    public static function find(WC_Order $order): ?self
    {

        $delivery_options = json_decode($order->get_meta('_myparcel_delivery_options'));

        $order_items = $order->get_items();

        $total_quantity = 0;
        foreach ($order_items as $item) {
            $total_quantity += $item->get_quantity();
        }

        return new self(
            id: $order->get_id(),
            key: $order->get_order_key(),
            number: $order->get_order_number(),
            status: $order->get_status(),
            total: Price::from($order->get_total()),
            subtotal: Price::from($order->get_subtotal()),
            total_quantity: $total_quantity,
            payment_method: $order->get_payment_method(),
            payment_method_title: lcfirst($order->get_payment_method_title()),
            shipping_address: $order->get_shipping_address_1(),
            shipping_postcode: $order->get_shipping_postcode(),
            shipping_city: $order->get_shipping_city(),
            shipping_method: $order->get_shipping_method(),
            shipping_total: Price::from($order->get_shipping_total()),
            has_free_shipping: $order->get_shipping_total() === '0',
            shipping_carrier: ucfirst($delivery_options?->carrier) ?? 'Postnl',
            expected_delivery_date: $delivery_options?->date ?? date('Y-m-d', strtotime('+3 days')),

            customer_email: $order->get_billing_email(),
            customer_company: $order->get_billing_company() ?: null,

            items: array_map(
                fn (WC_Order_Item_Product $item) => new OrderItem(
                    product: Product::find($item->get_product_id()),
                    quantity: $item->get_quantity(),
                    subtotal: Price::from($item->get_subtotal()),
                    total: Price::from($item->get_total()),
                ),
                $order_items
            ),
        );
    }

    public function toLivewire(): array
    {
        return ['id' => $this->id];
    }

    public static function fromLivewire($value): ?Order
    {
        $order = wc_get_order($value);
        return $order ? self::find($order) : null;
    }
}
