<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Checkout extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'woocommerce.checkout.payment-options',
    ];

    public function paymentMethods()
    {
        return WC()->payment_gateways()->payment_gateways();
    }

    public function with()
    {
        return [
            'payment_methods' => $this->paymentMethods(),
        ];
    }
}
