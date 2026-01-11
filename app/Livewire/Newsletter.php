<?php

namespace App\Livewire;

use App\Livewire\Forms\NewsletterForm;
use App\Services\MailchimpService;
use Livewire\Component;

class Newsletter extends Component
{
    public NewsletterForm $form;

    public bool $submitted = false;

    public function submit(MailchimpService $mailchimp): void
    {
        $this->validate();

        $success = $mailchimp->subscribe($this->form->email);

        if ($success) {
            $this->submitted = true;
            $this->form->reset();
        } else {
            $this->addError('form', __('Er is iets misgegaan. Probeer het later opnieuw.', 'sage'));
        }
    }

    public function render()
    {
        return view('livewire.newsletter');
    }
}
