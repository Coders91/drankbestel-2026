<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StarRating extends Component
{
    public function __construct(
        public float $rating = 0,
        public string $size = 'md',
        public bool $showEmpty = true,
    ) {}

    public function sizeClass(): string
    {
        return match ($this->size) {
            'xs' => 'size-3',
            'sm' => 'size-4',
            'lg' => 'size-6',
            default => 'size-5',
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.star-rating');
    }
}
