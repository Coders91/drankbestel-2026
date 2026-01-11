<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Usps extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  array  $usps  Array of USP items with title, subtitle (optional), icon, description (optional)
     * @param  string  $variant  Visual variant: 'grid', 'boxed', 'horizontal', 'minimal'
     * @param  int  $columns  Number of columns (2 or 4)
     * @param  bool  $showSubtitle  Whether to show the subtitle
     */
    public function __construct(
        public array $usps = [],
        public string $variant = 'grid',
        public int $columns = 4,
        public bool $showSubtitle = true
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.usps');
    }
}
