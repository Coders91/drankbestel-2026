<?php

namespace App\Livewire;

use App\Livewire\Concerns\SubmitsReviews;
use App\Livewire\Forms\ReviewForm;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProductReview extends Component
{
    use SubmitsReviews;

    #[Locked]
    public int $productId;

    public ReviewForm $form;

    public bool $submitted = false;

    public bool $isLoggedIn = false;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
        $this->isLoggedIn = is_user_logged_in();

        if ($this->isLoggedIn) {
            $user = wp_get_current_user();
            $this->form->author = $user->display_name;
            $this->form->email = $user->user_email;
        }
    }

    public function submit(): void
    {
        if ($this->submitReview()) {
            $this->submitted = true;
            $this->form->reset();
        }
    }

    public function render()
    {
        return view('livewire.product-review');
    }
}
