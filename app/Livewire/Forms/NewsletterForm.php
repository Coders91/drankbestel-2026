<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class NewsletterForm extends Form
{
    public string $email = '';

    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Vul je e-mailadres in.', 'sage'),
            'email.email' => __('Vul een geldig e-mailadres in.', 'sage'),
        ];
    }
}
