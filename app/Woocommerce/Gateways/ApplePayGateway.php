<?php

namespace App\Woocommerce\Gateways;

use App\Services\MollieService;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\RequestException;
use Mollie\Api\Types\PaymentMethod;
use WC_Payment_Gateway;

class ApplePayGateway extends WC_Payment_Gateway
{
    /**
     * @throws ApiException
     * @throws RequestException
     */
    public function __construct()
    {
        $mollieService = app(MollieService::class);
        $method = $mollieService->getPaymentMethod(PaymentMethod::APPLEPAY);

        $this->icon = get_svg('resources.images.logos.apple-pay', 'h-6');

        if ($method) {
            $this->id = 'mollie_applepay';
        } else {
            $this->id = 'applepay';
            $this->enabled = 'no';
        }

        $this->method_title = 'Apple Pay';
        $this->method_description = 'Pay with Apple Pay via Mollie.';
        $this->has_fields = true;
        $this->supports = ['products', 'refunds'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title', 'Apple Pay');
        $this->description = $this->get_option('description');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     * Define settings fields for WP Admin.
     */
    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled' => [
                'title' => 'Enable/Disable',
                'type' => 'checkbox',
                'label' => 'Enable Apple Pay',
                'default' => 'no',
            ],
            'title' => [
                'title' => 'Title',
                'type' => 'text',
                'default' => 'Apple Pay',
            ],
            'description' => [
                'title' => 'Description',
                'type' => 'textarea',
                'default' => 'Pay quickly and securely with Apple Pay.',
            ],
        ];
    }

    /**
     * Render Apple Pay button container.
     */
    public function payment_fields(): void
    {
        echo \Roots\view('partials.applepay-form')->render();
    }
}
