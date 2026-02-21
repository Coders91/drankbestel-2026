<?php

namespace App\Services\MyParcel;

use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use WC_Order;

class ShipmentBuilder
{
    public function build(WC_Order $order): AbstractConsignment
    {
        $config = config('myparcel');
        $consignment = ConsignmentFactory::createByCarrierId($config['carrier_id']);

        $consignment->setApiKey($config['api_key']);
        $consignment->setReferenceIdentifier((string) $order->get_id());

        $this->setRecipient($consignment, $order);
        $this->setDeliveryOptions($consignment, $order);
        $this->setShipmentOptions($consignment, $order, $config);
        $this->setLabelDescription($consignment, $order, $config);

        return $consignment;
    }

    protected function setRecipient(AbstractConsignment $consignment, WC_Order $order): void
    {
        $cc = $order->get_shipping_country() ?: $order->get_billing_country() ?: 'NL';

        $consignment->setCountry($cc);
        $consignment->setFullStreet($order->get_shipping_address_1() ?: $order->get_billing_address_1());
        $consignment->setPostalCode($order->get_shipping_postcode() ?: $order->get_billing_postcode());
        $consignment->setCity($order->get_shipping_city() ?: $order->get_billing_city());

        $person = trim(
            ($order->get_shipping_first_name() ?: $order->get_billing_first_name())
            .' '
            .($order->get_shipping_last_name() ?: $order->get_billing_last_name())
        );
        $consignment->setPerson($person);

        $company = $order->get_shipping_company() ?: $order->get_billing_company();
        if ($company) {
            $consignment->setCompany($company);
        }

        $consignment->setEmail($order->get_billing_email());
        $consignment->setPhone($order->get_billing_phone());
    }

    protected function setDeliveryOptions(AbstractConsignment $consignment, WC_Order $order): void
    {
        $raw = $order->get_meta('_myparcel_delivery_options');

        if (! $raw) {
            $consignment->setPackageType(AbstractConsignment::PACKAGE_TYPE_PACKAGE);
            $consignment->setDeliveryType(AbstractConsignment::DELIVERY_TYPE_STANDARD);

            return;
        }

        $data = is_string($raw) ? json_decode($raw, true) : (array) $raw;

        if (empty($data) || ! isset($data['deliveryType'])) {
            $consignment->setPackageType(AbstractConsignment::PACKAGE_TYPE_PACKAGE);
            $consignment->setDeliveryType(AbstractConsignment::DELIVERY_TYPE_STANDARD);

            return;
        }

        $adapter = DeliveryOptionsAdapterFactory::create($data);

        if ($adapter->getDeliveryTypeId()) {
            $consignment->setDeliveryType($adapter->getDeliveryTypeId());
        }

        if ($adapter->getPackageTypeId()) {
            $consignment->setPackageType($adapter->getPackageTypeId());
        } else {
            $consignment->setPackageType(AbstractConsignment::PACKAGE_TYPE_PACKAGE);
        }

        if ($adapter->getDate()) {
            $consignment->setDeliveryDate($adapter->getDate());
        }
    }

    protected function setShipmentOptions(AbstractConsignment $consignment, WC_Order $order, array $config): void
    {
        $options = $config['shipment_options'];

        $consignment->setSignature($options['signature'] ?? false);
        $consignment->setAgeCheck($options['age_check'] ?? false);
        $consignment->setLargeFormat($options['large_format'] ?? false);
        $consignment->setOnlyRecipient($options['only_recipient'] ?? false);

        if (! empty($options['insurance'])) {
            $consignment->setInsurance((int) $options['insurance']);
        }
    }

    protected function setLabelDescription(AbstractConsignment $consignment, WC_Order $order, array $config): void
    {
        $template = $config['label']['description'] ?? 'Bestelling #{order_number}';
        $description = str_replace('{order_number}', $order->get_order_number(), $template);

        $consignment->setLabelDescription(substr($description, 0, 45));
    }
}
