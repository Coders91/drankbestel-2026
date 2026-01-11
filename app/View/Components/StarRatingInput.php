<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StarRatingInput extends Component
{
    public function __construct(
    ) {

    }

    public function render(): View|Closure|string
    {
        return view('components.star-rating-input');
    }
}
