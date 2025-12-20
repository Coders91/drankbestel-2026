<?php

namespace App\Woocommerce\Gateways;

use App\Services\MollieService;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\RequestException;
use Mollie\Api\Types\PaymentMethod;

use WC_Payment_Gateway;

class CreditcardGateway extends WC_Payment_Gateway {

    /**
     * @throws ApiException
     * @throws RequestException
     */
    public function __construct() {

        $mollieService = app(MollieService::class);
        $method = $mollieService->getPaymentMethod(PaymentMethod::CREDITCARD);

        if ($method) {
            $this->id = 'mollie_creditcard';
            $this->icon = get_svg('resources.images.icons.credit-card');
        } else {
            $this->id = 'creditcard';
            $this->enabled = 'no';
        }

        $this->method_title = 'Creditcard';
        $this->method_description = 'Pay with your credit card via Mollie Components.';
        $this->has_fields = true;
        $this->supports = ['products', 'refunds'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title', 'Creditcard');
        $this->description = $this->get_option('description');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     * Define settings fields for WP Admin
     */
    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled' => [
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable Credit Card',
                'default' => 'no'
            ],
            'title' => [
                'title'   => 'Title',
                'type'    => 'text',
                'default' => 'Credit Card',
            ],
            'description' => [
                'title'   => 'Description',
                'type'    => 'textarea',
                'default' => 'Pay securely with your credit card.',
            ],
        ];
    }

    public function payment_fields() {
        return \Roots\View('partials.creditcard-form');
    }

}
