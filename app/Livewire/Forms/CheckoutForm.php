<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class CheckoutForm extends Form
{
    // Billing fields
    public string $billing_first_name = '';
    public string $billing_last_name = '';
    public string $billing_postcode = '';
    public string $billing_house_number = '';
    public ?string $billing_house_number_suffix = '';

    // Shipping fields
    public string $shipping_first_name = '';
    public string $shipping_last_name = '';
    public string $shipping_postcode = '';
    public string $shipping_house_number = '';
    public ?string $shipping_house_number_suffix = '';

    public ?bool $billing_address_found = null;
    public ?bool $shipping_address_found = null;

    // Filled with api read only required if address has to be filled manually
    public string $billing_street_name = '';
    public string $billing_city = '';
    public string $shipping_street_name = '';
    public string $shipping_city = '';

    public string $billing_address_1 = '';
    public string $shipping_address_1 = '';

    // Contact fields
    public string $billing_email = '';
    public string $billing_phone = '';

    // Checkbox
    public bool $ship_to_different_address = false;

    // Radio
    public bool $is_business_order = false;

    // Business fields
    public string $billing_company = '';
    public string $vat_number = '';
    public string $customer_reference = '';

    // Payment method
    public string $payment_method = 'mollie_ideal';

    // Checkboxes
    public bool $newsletter = false;
    public bool $age_check = false;

    public function rules(): array
    {
        return [
            'billing_first_name' => 'required|min:2|alpha',
            'billing_last_name' => 'required|alpha',
            'billing_postcode' => 'required|regex:/^[\d]{4}\s?[a-zA-Z]{2,3}$/',
            'billing_house_number' => 'required|numeric',
            'billing_house_number_suffix' => 'nullable',
            'shipping_first_name' => 'required_if:ship_to_different_address,true|min:2|alpha',
            'shipping_last_name' => 'required_if:ship_to_different_address,true|alpha',
            'shipping_postcode' => 'required_if:ship_to_different_address,true|regex:/^[\d]{4}\s?[a-zA-Z]{2,3}$/',
            'shipping_house_number' => 'required_if:ship_to_different_address,true|numeric',
            'shipping_house_number_suffix' => 'nullable',
            'billing_address_found' => 'nullable|boolean',
            'shipping_address_found' => 'nullable|boolean',
            'billing_street_name' => 'required_if:billing_address_found,false',
            'billing_city' => 'required_if:billing_address_found,false',
            'shipping_street_name' => 'required_if:shipping_address_found,false',
            'shipping_city' => 'required_if:shipping_address_found,false',
            'billing_email' => 'required|email',
            'billing_phone' => 'required|min:8|regex:/^[\d+()\-\s]{8,}$/',
            'ship_to_different_address' => 'nullable|boolean',
            'is_business_order' => 'nullable|boolean',
            'billing_company' => 'required_if:is_business_order,true',
            'vat_number' => 'sometimes|regex:/^NL\d{9}B\d{2}$/',
            'customer_reference' => 'nullable',
            'payment_method' => 'required',
            'newsletter' => 'nullable',
            'age_check' => 'accepted|required',
        ];
    }

    public function messages(): array
    {
        return [
            // Billing first name
            'billing_first_name.required' => 'Er is geen voornaam ingevuld.',
            'billing_first_name.min' => 'Voornaam moet minimaal 2 karakters bevatten.',
            'billing_first_name.alpha' => 'Er is geen geldige voornaam ingevuld.',

            // Billing last name
            'billing_last_name.required' => 'Er is geen achternaam ingevuld.',
            'billing_last_name.alpha' => 'Er is geen geldige achternaam ingevuld.',

            // Billing postcode
            'billing_postcode.required' => 'Er is geen postcode ingevuld.',
            'billing_postcode.regex' => 'Er is geen geldige postcode ingevuld.',

            // Billing house number
            'billing_house_number.required' => 'Er is geen huisnummer ingevuld.',
            'billing_house_number.numeric' => 'Er is geen geldig huisnummer ingevuld.',

            // Billing street name
            'billing_street_name.required_if' => 'Er is geen straatnaam ingevuld.',

            // Billing city
            'billing_city.required_if' => 'Er is geen plaats ingevuld.',

            // Billing email
            'billing_email.required' => 'Er is geen e-mailadres ingevuld.',
            'billing_email.email' => 'Er is geen geldig e-mailadres ingevuld.',

            // Billing phone
            'billing_phone.required' => 'Er is geen telefoonnummer ingevuld.',
            'billing_phone.min' => 'Er is een te kort telefoonnummer ingevuld.',
            'billing_phone.regex' => 'Er is geen geldig telefoonnummer ingevuld.',

            // Billing company
            'billing_company.required_if' => 'Er is geen bedrijfsnaam ingevuld.',

            // Shipping first name
            'shipping_first_name.required_if' => 'Er is geen voornaam ingevuld.',
            'shipping_first_name.min' => 'Voornaam moet minimaal 2 karakters bevatten.',
            'shipping_first_name.alpha' => 'Er is geen geldige voornaam ingevuld',

            // Shipping last name
            'shipping_last_name.required_if' => 'Er is geen achternaam ingevuld.',
            'shipping_last_name.alpha' => 'Er is geen geldige achternaam ingevuld.',

            // Shipping postcode
            'shipping_postcode.required_if' => 'Er is geen postcode ingevuld.',
            'shipping_postcode.regex' => 'Er is geen geldige postcode ingevuld.',

            // Shipping house number
            'shipping_house_number.required_if' => 'Er is geen huisnummer ingevuld.',
            'shipping_house_number.numeric' => 'Er is geen geldig huisnummer ingevuld.',

            // Shipping street name
            'shipping_street_name.required_if' => 'Er is geen straatnaam ingevuld.',

            // Shipping city
            'shipping_city.required_if' => 'Er is geen plaats ingevuld.',

            // VAT number
            'vat_number.regex' => 'Er is geen geldig btw-nummer ingevuld.',

            // Payment method
            'payment_method.required' => 'Er is geen betaalmethode geselecteerd.',

            // Age check
            'age_check.required' => 'Je moet bevestigen dat je 18 jaar of ouder bent.',
            'age_check.accepted' => 'Je moet bevestigen dat je 18 jaar of ouder bent.',
        ];
    }
}
