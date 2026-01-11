<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SizeSelector extends Component
{
    public array $variations;

    public bool $showSelect;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $sizeVariations = [],
    ) {
        $this->variations = $sizeVariations;
        $this->showSelect = count($sizeVariations) > 3;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.size-selector');
    }
}
