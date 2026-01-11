<?php

namespace App\Livewire;

use App\Livewire\Forms\ContactForm;
use Livewire\Component;

class Contact extends Component
{
    public ContactForm $form;

    public bool $submitted = false;

    public function submit(): void
    {
        $this->validate();

        $contactEmail = config('store.contact.email');
        $subject = sprintf(
            __('[DrankBestel.nl] Nieuw contactbericht van %s', 'sage'),
            $this->form->name
        );

        $body = $this->buildEmailBody();
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            sprintf('Reply-To: %s <%s>', $this->form->name, $this->form->email),
        ];

        $sent = wp_mail($contactEmail, $subject, $body, $headers);

        if ($sent) {
            $this->submitted = true;
            $this->form->reset();
        } else {
            $this->addError('form', __('Er is iets misgegaan bij het versturen van je bericht. Probeer het later opnieuw.', 'sage'));
        }
    }

    protected function buildEmailBody(): string
    {
        return sprintf(
            '<html><body>
            <h2>%s</h2>
            <p><strong>%s:</strong> %s</p>
            <p><strong>%s:</strong> %s</p>
            <p><strong>%s:</strong></p>
            <div style="white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 5px;">%s</div>
            </body></html>',
            __('Nieuw contactbericht', 'sage'),
            __('Naam', 'sage'),
            esc_html($this->form->name),
            __('E-mailadres', 'sage'),
            esc_html($this->form->email),
            __('Bericht', 'sage'),
            esc_html($this->form->message)
        );
    }

    public function render()
    {
        return view('livewire.contact')->layoutData(['breadcrumbs' => true]);
    }
}
