<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Method;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\RequestException;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Http\Data\Money;

class MollieService
{
    protected MollieApiClient $mollie;

    private ?iterable $enabledMethods = null;

    public function __construct(MollieApiClient $mollie)
    {
        $this->mollie = $mollie;
    }

    /**
     * @throws RequestException
     * @throws ApiException
     */
    public function getPaymentMethod(string $id): ?Method
    {
        if ($this->enabledMethods === null) {
            $this->enabledMethods = $this->mollie->methods->allEnabled();
        }

        foreach ($this->enabledMethods as $method) {
            if ($method->id === $id) {
                return $method;
            }
        }

        return null;
    }

    /**
     * @throws ApiException
     * @throws RequestException
     */
    public function getPayment(string $paymentId): ?Payment
    {
        try {
            return $this->mollie->payments->get($paymentId);
        } catch (ApiException $e) {
            Log::error('Failed to get Mollie payment: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the Mollie payment method ID from WooCommerce gateway ID
     */
    public function getMollieMethodFromGateway(string $gatewayId): ?string
    {
        $map = [
            'mollie_ideal' => 'ideal',
            'mollie_creditcard' => 'creditcard',
            'mollie_paypal' => 'paypal',
            'mollie_bancontact' => 'bancontact',
            'mollie_applepay' => 'applepay',
            'mollie_googlepay' => 'googlepay',
        ];

        return $map[$gatewayId] ?? null;
    }

    /**
     * @throws ApiException
     * @throws RequestException
     */
    public function createPayment(
        string $method, float $amount, string $description, string $redirect_url, string $webhook_url, array $metadata = [], array $args = []
    ): ?Payment
    {

        // Skip the enabled-methods check for Apple Pay — availability is already
        // verified client-side via canMakePayments() and merchant validation.
        if ($method !== 'applepay' && ! $this->getPaymentMethod($method)) {
            Log::error('payment method not enabled');
            return null;
        }

        $money = new Money('EUR', number_format($amount, 2, '.', ''));

        $payment = [
            'amount'      => $money,
            'description' => $description,
            'redirectUrl' => $redirect_url,
            'webhookUrl'  => $webhook_url,
            'method'      => $method,
            'metadata'    => $metadata
        ];

        if(!empty($args)) {
            $payment = array_merge($args, $payment);
        }

        return $this->mollie->payments->create($payment);
    }

    /**
     * Request Apple Pay payment session for merchant validation.
     *
     * @throws ApiException
     * @throws RequestException
     */
    public function requestApplePaySession(string $validationUrl): array
    {
        $domain = parse_url(home_url(), PHP_URL_HOST);

        return $this->mollie->wallets->requestApplePayPaymentSession($domain, $validationUrl)->toArray();
    }
}
