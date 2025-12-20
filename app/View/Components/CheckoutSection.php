<?php

namespace App\View\Components;

use Roots\Acorn\View\Component;

class CheckoutSection extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $title,
        public string $titleClass = 'mb-4',
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        return $this->view('components.checkout-section');
    }
}
