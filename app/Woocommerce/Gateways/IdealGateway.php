<?php

namespace App\Woocommerce\Gateways;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\RequestException;
use App\Services\MollieService;
use Mollie\Api\Types\PaymentMethod;
use WC_Payment_Gateway;

class IdealGateway extends WC_Payment_Gateway {

    /**
     * @throws ApiException
     * @throws RequestException
     */
    public function __construct() {

        $mollieService = app(MollieService::class);
        $method = $mollieService->getPaymentMethod(PaymentMethod::IDEAL);
        $this->icon = get_svg('resources.images.logos.ideal-wero', 'size-[30px]');

        if($method) {
            $this->id = 'mollie_ideal';
        } else {
            $this->id = 'ideal';
            $this->enabled = 'no';
        }

        $this->has_fields = false;
        $this->method_title = 'iDEAL';
        $this->method_description = 'iDEAL payment via Mollie.';
        $this->supports = ['products', 'refunds'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title', 'iDEAL');
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
                'label'   => 'Enable iDEAL',
                'default' => 'yes'
            ],
            'title' => [
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'iDEAL | Wero',
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => 'Description',
                'type'        => 'textarea',
                'description' => 'Payment method description that the customer will see on your checkout.',
                'default'     => 'Pay securely with your own bank.',
            ],
        ];
    }
}
