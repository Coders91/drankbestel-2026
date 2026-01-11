<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class ContactForm extends Form
{
    public string $name = '';

    public string $email = '';

    public string $message = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'message' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Vul je naam in.', 'sage'),
            'name.min' => __('Naam moet minimaal 2 karakters bevatten.', 'sage'),
            'name.max' => __('Naam mag maximaal 100 karakters bevatten.', 'sage'),
            'email.required' => __('Vul je e-mailadres in.', 'sage'),
            'email.email' => __('Vul een geldig e-mailadres in.', 'sage'),
            'message.required' => __('Vul je bericht in.', 'sage'),
            'message.min' => __('Bericht moet minimaal 10 karakters bevatten.', 'sage'),
            'message.max' => __('Bericht mag maximaal 2000 karakters bevatten.', 'sage'),
        ];
    }
}
