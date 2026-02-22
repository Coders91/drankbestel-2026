<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

use App\Livewire\Forms\CheckoutForm;
use App\Services\KadasterService;
use App\Services\MailchimpService;
use App\Services\MollieService;

use WC_Order;
use WC_Order_Item_Fee;
use WC_Shipping_Rate;

use Exception;
use Throwable;

class Checkout extends Component
{
    const PENDING_ORDER_KEY = 'pending_checkout_order_id';
    const SESSION_KEY = 'checkoutForm';

    public CheckoutForm $form;

    protected string $postalCode = '';
    protected string $houseNumber = '';
    protected ?string $houseNumberSuffix = '';
    protected string $type = 'billing';

    protected string $checkedBillingPostcode = '';
    protected string $checkedBillingHouseNumber = '';
    protected ?string $checkedBillingSuffix = null;

    protected string $checkedShippingPostcode = '';
    protected string $checkedShippingHouseNumber = '';
    protected ?string $checkedShippingSuffix = null;

    public ?array $deliverySelection = null;

    public array $messages = [];

    public bool $isProcessing = false;

    private KadasterService $kadasterService;
    private MollieService $mollieService;

    public function boot(KadasterService $kadasterService, MollieService $mollieService)
    {
        $this->kadasterService = $kadasterService;
        $this->mollieService = $mollieService;

        if (WC()->cart->is_empty()) {
            $pendingOrderId = WC()->session->get(self::PENDING_ORDER_KEY);

            if ($pendingOrderId) {
                $order = wc_get_order($pendingOrderId);

                if ($order && $order->get_status() === 'pending') {
                    $this->restoreCartFromOrder($order);
                    return null;
                }

                WC()->session->set(self::PENDING_ORDER_KEY, null);
            }

            return redirect()->route('cart');
        }
    }

    public function mount()
    {
        if (function_exists('WC') && WC()->session) {
            $data = WC()->session->get(self::SESSION_KEY, []);
            $formData = $data['form_data'] ?? $data;

            $this->form->fill($formData);
            $this->messages = $data['messages'] ?? [];

            $checkedState = $data['checked_state'] ?? [];

            $this->checkedBillingPostcode = $checkedState['billing']['postcode'] ?? '';
            $this->checkedBillingHouseNumber = $checkedState['billing']['house_number'] ?? '';
            $this->checkedBillingSuffix = $checkedState['billing']['suffix'] ?? null;

            $this->checkedShippingPostcode = $checkedState['shipping']['postcode'] ?? '';
            $this->checkedShippingHouseNumber = $checkedState['shipping']['house_number'] ?? '';
            $this->checkedShippingSuffix = $checkedState['shipping']['suffix'] ?? null;
        }
    }

    protected function saveStateToSession(): void
    {
        if (function_exists('WC') && WC()->session) {
            WC()->session->set(self::SESSION_KEY, [
                'form_data' => $this->form->all(),
                'messages' => $this->messages,
                'checked_state' => [
                    'billing' => [
                        'postcode' => $this->checkedBillingPostcode,
                        'house_number' => $this->checkedBillingHouseNumber,
                        'suffix' => $this->checkedBillingSuffix,
                    ],
                    'shipping' => [
                        'postcode' => $this->checkedShippingPostcode,
                        'house_number' => $this->checkedShippingHouseNumber,
                        'suffix' => $this->checkedShippingSuffix,
                    ],
                ],
            ]);
        }
    }

    public function updated($propertyName): void
    {
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

        if ($propertyName === 'form.ship_to_different_address') {
            $shippingFields = [
                'shipping_first_name',
                'shipping_last_name',
                'shipping_postcode',
                'shipping_house_number',
                'shipping_street_name',
                'shipping_city',
            ];

            foreach ($shippingFields as $field) {
                if (empty($this->form->$field)) {
                    $this->resetErrorBag("form.{$field}");
                }
            }
        }
    }

    public function updatedForm(): void
    {
        $this->saveStateToSession();
    }

    /**
     * Restore cart items from a pending order (when user clicks back from payment)
     */
    private function restoreCartFromOrder(WC_Order $order): void
    {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            $variation_id = $item->get_variation_id();

            WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
        }

        // Cancel the pending order since we're restoring the cart for a new attempt
        $order->update_status('cancelled', __('Order cancelled: customer returned from payment page.', 'sage'));

        // Clear the pending order ID
        WC()->session->set(self::PENDING_ORDER_KEY, null);
    }

    /** @throws Exception */
    public function validateAddress($type, $postcode, $house_number, $house_number_suffix = null) : bool
    {
        $valid = false;
        $house_number = (string) $house_number;

        if ($type === 'billing') {
            if ($this->checkedBillingPostcode === $postcode &&
                $this->checkedBillingHouseNumber === $house_number &&
                $this->checkedBillingSuffix === $house_number_suffix
            ) {
                return true;
            }
        } elseif ($type === 'shipping') {
            if ($this->checkedShippingPostcode === $postcode &&
                $this->checkedShippingHouseNumber === $house_number &&
                $this->checkedShippingSuffix === $house_number_suffix
            ) {
                return true;
            }
        }

        try {
            $address = $this->kadasterService->validateAddress($postcode, (int) $house_number, $house_number_suffix);

            if ($type === 'billing') {
                $this->checkedBillingPostcode = $postcode;
                $this->checkedBillingHouseNumber = $house_number;
                $this->checkedBillingSuffix = $house_number_suffix;
            } elseif ($type === 'shipping') {
                $this->checkedShippingPostcode = $postcode;
                $this->checkedShippingHouseNumber = $house_number;
                $this->checkedShippingSuffix = $house_number_suffix;
            }

            if($address->first()) {

                $valid = true;

                if($type === 'billing') {
                    $this->form->billing_address_found = true;
                    $this->resetValidation(['form.billing_street_name', 'form.billing_city']);
                } elseif($type === 'shipping') {
                    $this->form->shipping_address_found = true;
                    $this->resetValidation(['form.shipping_street_name', 'form.shipping_city']);
                }

                $this->messages[$type . '_address'] = '';

                $this->postalCode = $postcode;
                $this->houseNumber = $house_number;
                $this->houseNumberSuffix = $house_number_suffix;
                $this->type = $type;

                $address = $address->first();

                $street_name = $address['street_name'] ?? '';
                $city = $address['city'] ?? '';
                $address_1 = $address['address_line_1'] ?? '';
            } else {

                if($type === 'billing') {
                    $this->form->billing_address_found = false;
                } elseif($type === 'shipping') {
                    $this->form->shipping_address_found = false;
                }

                $this->messages[$type . '_address'] = 'We konden deze combinatie postcode en huisnummer niet vinden. Vul hieronder handmatig je straat en plaatsnaam in.';

                // Reset on not found address
                $this->postalCode = '';
                $this->houseNumber = '';
                $this->houseNumberSuffix = '';

                $address = null;
                $street_name = '';
                $city = '';
                $address_1 = '';
            }

            if ($type === 'billing') {
                $this->form->billing_street_name = $street_name;
                $this->form->billing_city = $city;
                $this->form->billing_address_1 = $address_1;
            } elseif ($type === 'shipping') {
                $this->form->shipping_street_name = $street_name;
                $this->form->shipping_city = $city;
                $this->form->shipping_address_1 = $address_1;
            }

        } catch (Exception $e) {
            Log::error($e);
        }

        $this->saveStateToSession();

        return $valid;
    }

    public function isAddressReadOnly($type): bool
    {
        $state = $type === 'billing' ? $this->form->billing_address_found : $this->form->shipping_address_found;
        return $state !== false;
    }

    /**
     * Handle Apple Pay payment submission
     */
    public function saveApplePayPayment(string $applePayToken)
    {
        $this->validate();

        $this->isProcessing = true;

        try {
            // Create order using shared method
            $order = $this->createOrder();

            // Create Mollie payment with Apple Pay token
            $redirectUrl = route('payment.return', [
                'order_id' => $order->get_id(),
                'key' => $order->get_order_key(),
            ]);

            $webhookUrl = home_url('/wp-json/mollie/v1/webhook');

            $payment = $this->mollieService->createPayment(
                method: 'applepay',
                amount: $order->get_total(),
                description: sprintf('Bestelling #%s', $order->get_order_number()),
                redirect_url: $redirectUrl,
                webhook_url: $webhookUrl,
                metadata: [
                    'order_id' => $order->get_id(),
                    'order_key' => $order->get_order_key(),
                ],
                args: ['applePayPaymentToken' => $applePayToken],
            );

            if (! $payment) {
                throw new Exception('Kan geen betaling aanmaken. Probeer het later opnieuw.');
            }

            // Store Mollie payment ID
            $order->update_meta_data('_mollie_payment_id', $payment->id);
            $order->add_order_note(sprintf(
                __('Mollie Apple Pay payment created (Payment ID: %s)', 'sage'),
                $payment->id
            ));
            $order->save();

            // Clear cart and session
            WC()->cart->empty_cart();
            WC()->session->set(self::PENDING_ORDER_KEY, null);
            WC()->session->set(self::SESSION_KEY, null);

            // Redirect to thank you page
            return $this->redirect($redirectUrl);

        } catch (Throwable $e) {
            $this->isProcessing = false;
            Log::error('Apple Pay checkout error: ' . $e->getMessage());
            $this->addError('order', 'Er is een fout opgetreden bij het plaatsen van uw bestelling: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Create a WooCommerce order from the current form data and cart.
     */
    private function createOrder(): WC_Order
    {
        $order = wc_create_order();

        // Assign user if logged in
        if (is_user_logged_in()) {
            $order->set_customer_id(get_current_user_id());
        }

        // Add products from cart
        if (WC()->cart && !WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $order->add_product(
                    $cart_item['data'],
                    $cart_item['quantity'],
                    [
                        'subtotal' => $cart_item['line_subtotal'],
                        'total'    => $cart_item['line_total'],
                        'subtotal_tax' => $cart_item['line_subtotal_tax'],
                        'total_tax'    => $cart_item['line_tax'],
                    ]
                );
            }
        }

        // Add fees
        foreach (WC()->cart->get_fees() as $fee) {
            $item = new WC_Order_Item_Fee();
            $item->set_name($fee->name);
            $item->set_total($fee->amount);
            $item->set_tax_class($fee->tax_class);
            $item->set_total_tax($fee->tax);
            $order->add_item($item);
        }

        // Add shipping method (if any)
        $chosen_shipping = WC()->session->get('chosen_shipping_methods');
        if ($chosen_shipping) {
            foreach ($chosen_shipping as $method_id) {
                $rate = new WC_Shipping_Rate($method_id);
                $order->add_shipping($rate);
            }
        }

        // Set addresses
        $order->set_address($this->billingAddressArray());

        if ($this->form->ship_to_different_address) {
            $order->set_address($this->shippingAddressArray(), 'shipping');
        } else {
            $order->set_address($this->billingAddressArray(), 'shipping');
        }

        // Business data meta
        if ($this->form->is_business_order) {
            $order->set_billing_company($this->form->billing_company);
            $order->update_meta_data('_vat_number', $this->form->vat_number);
            $order->update_meta_data('_customer_reference', $this->form->customer_reference);
        } else {
            // Reset business fields to null/empty
            $this->form->billing_company = '';
            $this->form->vat_number = '';
            $this->form->customer_reference = '';
        }

        // Delivery options
        if (!empty($this->deliverySelection)) {
            $order->update_meta_data(
                '_myparcel_delivery_options',
                json_encode($this->deliverySelection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        // Payment method
        $gateway = WC()->payment_gateways()->payment_gateways()[$this->form->payment_method] ?? null;
        if ($gateway) {
            $order->set_payment_method($this->form->payment_method);
            $order->set_payment_method_title($gateway->title);
        }

        $order->calculate_totals();

        // Set status to pending payment
        $order->set_status('pending');

        // Save order
        $order->save();

        return $order;
    }

    public function save($cardToken = '')
    {
        $this->validate();

        $this->isProcessing = true;

        try {
            // Create order
            $order = $this->createOrder();

            // Subscribe to newsletter if opted in
            if ($this->form->newsletter) {
                try {
                    app(MailchimpService::class)->subscribe($this->form->billing_email);
                } catch (Throwable $e) {
                    Log::warning('Newsletter subscription failed: ' . $e->getMessage());
                }
            }

            // Create Mollie payment
            $mollieMethod = $this->mollieService->getMollieMethodFromGateway($this->form->payment_method);

            if (! $mollieMethod) {
                throw new Exception('Ongeldige betaalmethode geselecteerd.');
            }

            $redirectUrl = route('payment.return', [
                'order_id' => $order->get_id(),
                'key' => $order->get_order_key(),
            ]);

            $cardToken = $cardToken ? ['cardToken' => $cardToken] : [];

            $payment = $this->mollieService->createPayment(
                method: $mollieMethod,
                amount: $order->get_total(),
                description: sprintf('Bestelling #%s', $order->get_order_number()),
                redirect_url: $redirectUrl,
                webhook_url: config('services.mollie.webhook_url'),
                metadata: [
                    'order_id' => $order->get_id(),
                    'order_key' => $order->get_order_key(),
                ],
                args: $cardToken,
            );

            if (! $payment) {
                throw new Exception('Kan geen betaling aanmaken. Probeer het later opnieuw.');
            }

            // Store Mollie payment ID
            $order->update_meta_data('_mollie_payment_id', $payment->id);
            $order->add_order_note(sprintf(
                __('Mollie payment created (Payment ID: %s)', 'sage'),
                $payment->id
            ));
            $order->save();

            // Store pending order ID for back button handling
            WC()->session->set(self::PENDING_ORDER_KEY, $order->get_id());

            return $this->redirect($payment->getCheckoutUrl());

        } catch (Throwable $e) {
            $this->isProcessing = false;
            Log::error('Checkout error: ' . $e->getMessage());
            $this->addError('order', 'Er is een fout opgetreden bij het plaatsen van uw bestelling: ' . $e->getMessage());
        }

        return null;
    }

    private function billingAddressArray(): array
    {
        return [
            'first_name' => $this->form->billing_first_name,
            'last_name'  => $this->form->billing_last_name,
            'email'      => $this->form->billing_email,
            'phone'      => $this->form->billing_phone,
            'address_1'  => $this->form->billing_address_1,
            'street_name' => $this->form->billing_street_name,
            'house_number' => $this->form->billing_house_number,
            'house_number_suffix' => $this->form->billing_house_number_suffix,
            'postcode'   => $this->form->billing_postcode,
            'city'       => $this->form->billing_city,
            'country'    => 'NL',
        ];
    }

    private function shippingAddressArray(): array
    {
        return [
            'first_name' => $this->form->shipping_first_name,
            'last_name'  => $this->form->shipping_last_name,
            'address_1'  => $this->form->shipping_address_1,
            'street_name' => $this->form->shipping_street_name,
            'house_number' => $this->form->shipping_house_number,
            'house_number_suffix' => $this->form->shipping_house_number_suffix,
            'postcode'   => $this->form->shipping_postcode,
            'city'       => $this->form->shipping_city,
            'country'    => 'NL',
        ];
    }

    public function render()
    {
        return view('livewire.checkout')
            ->layoutData(['header' => false, 'breadcrumbs' => false, 'footer' => false]);
    }
}
