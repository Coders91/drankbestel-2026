<?php

namespace App\Woocommerce\Gateways;

use App\Services\MollieService;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\RequestException;
use Mollie\Api\Types\PaymentMethod;
use WC_Payment_Gateway;

class PaypalGateway extends WC_Payment_Gateway {

    /**
     * @throws ApiException
     * @throws RequestException
     */
    public function __construct() {

        $mollieService = app(MollieService::class);
        $method = $mollieService->getPaymentMethod(PaymentMethod::PAYPAL);

        if ($method) {
            $this->id = 'mollie_paypal';
            $this->icon = get_svg('resources.images.logos.paypal');
        } else {
            $this->id = 'paypal';
            $this->enabled = 'no';
        }

        $this->has_fields = false;
        $this->method_title = 'PayPal';
        $this->method_description = 'PayPal is the most popular payment gateway.';
        $this->supports = ['products', 'refunds'];
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields(): void
    {
        $this->form_fields = [
              'enabled' => [
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable PayPal', 'woocommerce'),
                'default' => 'no'
              ],
              'title' => [
                'title' => __('Title', 'woocommerce'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default' => __('PayPal', 'woocommerce'),
                'desc_tip' => true
              ],
              'description' => [
                'title' => __('Description', 'woocommerce'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default' => __('Pay with PayPal', 'woocommerce'),
                'desc_tip' => true
              ],
        ];
    }
}
