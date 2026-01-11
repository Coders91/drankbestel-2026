<?php

namespace App\View\Models;

use Illuminate\Support\Facades\Vite;
use WC_Order;
use WC_Order_Item_Product;

readonly class InvoiceData
{
    public function __construct(
        // Invoice identifiers
        public string $invoice_number,
        public string $invoice_date,

        // Order info
        public int $order_id,
        public string $order_number,
        public string $order_date,
        public string $payment_method,

        // Customer billing info
        public string $billing_name,
        public ?string $billing_company,
        public string $billing_address,
        public string $billing_postcode,
        public string $billing_city,
        public string $billing_country,
        public string $billing_email,
        public ?string $billing_phone,

        // Custom business fields
        public ?string $customer_vat_number,
        public ?string $customer_reference,

        // Shipping info
        public string $shipping_name,
        public string $shipping_address,
        public string $shipping_postcode,
        public string $shipping_city,
        public string $shipping_country,

        // Line items
        public array $items,

        // Totals
        public Price $subtotal,
        public Price $shipping_total,
        public Price $discount_total,
        public Price $tax_total,
        public Price $total,
        public array $tax_lines,

        // Company info (seller)
        public string $company_name,
        public string $company_address,
        public string $company_city,
        public string $company_postcode,
        public string $company_country,
        public ?string $company_vat,
        public ?string $company_kvk,
        public ?string $company_phone,
        public ?string $company_email,
        public ?string $company_logo,
        public ?string $footer_text,
    ) {}

    public static function fromOrder(WC_Order $order, string $invoiceNumber, string $invoiceDate): self
    {
        $totalAmount = $order->get_total();
        $taxTotalAmount = $order->get_total_tax();

        // Calculate "Gross Discount" (Net + Tax) so €8,26 becomes €10,00
        $discountTotalAmount = (float) $order->get_discount_total() + (float) $order->get_discount_tax();

        $taxLines = [];
        $rawTaxTotals = $order->get_tax_totals();

        if (!empty($rawTaxTotals)) {
            foreach ($rawTaxTotals as $code => $tax) {
                $taxLines[] = [
                    'label' => $tax->label,
                    'amount' => Price::from($tax->amount),
                ];
            }
        } elseif ($totalAmount > 0) {
            // If WC returns no taxes, calculate 21% BTW backwards (Inclusief BTW logic)
            // Formula: Total - (Total / 1.21)
            $calculatedVat = $totalAmount - ($totalAmount / 1.21);
            $taxTotalAmount = $calculatedVat;
            $taxLines[] = [
                'label' => 'BTW (21%)',
                'amount' => Price::from($calculatedVat),
            ];
        }

        $items = [];
        foreach ($order->get_items() as $item) {
            if ($item instanceof WC_Order_Item_Product) {
                $quantity = $item->get_quantity();

                $lineTotalIncl = (float) $item->get_total() + (float) $item->get_total_tax();
                $lineTax = (float) $item->get_total_tax();

                // If WC says 0 tax, but we know there should be tax (based on the order total)
                if ($lineTax <= 0 && $taxTotalAmount > 0) {
                    $lineTax = $lineTotalIncl - ($lineTotalIncl / 1.21);
                    $displayRate = "21%";
                } else {
                    $lineSubtotalExcl = (float)$item->get_subtotal();
                    $displayRate = ($lineSubtotalExcl > 0)
                        ? round(($lineTax / $lineSubtotalExcl) * 100) . '%'
                        : "21%";
                }

                $unitPriceIncl = $quantity > 0 ? $lineTotalIncl / $quantity : 0;

                $items[] = new InvoiceLineItem(
                    product: Product::find($item->get_product()),
                    name: $item->get_name(),
                    quantity: $quantity,
                    unit_price: Price::from($unitPriceIncl),
                    subtotal: Price::from($lineTotalIncl),
                    tax: Price::from($lineTax),
                    total: Price::from($lineTotalIncl),
                    sku: $item->get_product()?->get_sku() ?? '',
                    tax_rate: $displayRate,
                );
            }
        }

        // 4. Fetch Company & Store Info
        $countries = WC()->countries->get_countries();
        $storeAddress = get_option('woocommerce_store_address', '');
        $storeAddress2 = get_option('woocommerce_store_address_2', '');
        $fullCompanyAddress = trim($storeAddress . ($storeAddress2 ? ', ' . $storeAddress2 : ''));

        $storeCountryState = get_option('woocommerce_default_country', 'NL');
        $storeCountryCode = explode(':', $storeCountryState)[0];
        $storeCountry = $countries[$storeCountryCode] ?? $storeCountryCode;

        return new self(
            invoice_number: $invoiceNumber,
            invoice_date: $invoiceDate,

            order_id: $order->get_id(),
            order_number: $order->get_order_number(),
            order_date: $order->get_date_created()?->date('d-m-Y') ?? '',
            payment_method: $order->get_payment_method_title(),

            // Billing
            billing_name: trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            billing_company: $order->get_billing_company() ?: null,
            billing_address: $order->get_billing_address_1(),
            billing_postcode: $order->get_billing_postcode(),
            billing_city: $order->get_billing_city(),
            billing_country: $countries[$order->get_billing_country()] ?? $order->get_billing_country(),
            billing_email: $order->get_billing_email(),
            billing_phone: $order->get_billing_phone() ?: null,

            customer_vat_number: $order->get_meta('_vat_number') ?: null,
            customer_reference: $order->get_meta('_customer_reference') ?: null,

            // Shipping (with fallback to billing if empty)
            shipping_name: trim($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name()) ?: trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            shipping_address: $order->get_shipping_address_1() ?: $order->get_billing_address_1(),
            shipping_postcode: $order->get_shipping_postcode() ?: $order->get_billing_postcode(),
            shipping_city: $order->get_shipping_city() ?: $order->get_billing_city(),
            shipping_country: $countries[$order->get_shipping_country()] ?? $countries[$order->get_billing_country()] ?? $order->get_billing_country(),

            items: $items,

            // Totals
            subtotal: Price::from($order->get_subtotal()),
            shipping_total: Price::from($order->get_shipping_total()),
            discount_total: Price::from($discountTotalAmount),
            tax_total: Price::from($taxTotalAmount),
            total: Price::from($totalAmount),
            tax_lines: $taxLines,

            // Company
            company_name: config('store.details.name'),
            company_address: $fullCompanyAddress,
            company_city: get_option('woocommerce_store_city', ''),
            company_postcode: get_option('woocommerce_store_postcode', ''),
            company_country: $storeCountry,
            company_vat: config('store.details.btw'),
            company_kvk: config('store.details.kvk'),
            company_phone: config('store.contact.phone'),
            company_email: config('store.contact.email') ?: get_option('woocommerce_email_from_address'),
            company_logo: Vite::asset('resources/images/logos/drankbestel.svg'),
            footer_text: config('services.invoice.footer_text'),
        );
    }
}
