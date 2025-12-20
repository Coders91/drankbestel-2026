<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use App\View\Models\Product as ProductModel;

class Product extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ProductModel $product)
    {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.woocommerce.product');
    }
}
