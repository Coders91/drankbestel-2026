<?php

namespace App\Livewire;

use App\Livewire\Concerns\SubmitsReviews;
use App\Livewire\Forms\ReviewForm;
use App\View\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProductReviewPage extends Component
{
    use SubmitsReviews;

    #[Locked]
    public int $productId;

    public ReviewForm $form;

    public bool $submitted = false;

    public bool $isLoggedIn = false;

    public ?Product $product = null;

    public function mount(string $product_slug): void
    {
        // Reject slugs ending with size patterns (e.g., -70cl, -100cl, -1l, -750ml)
        if (preg_match('/-\d+(?:cl|ml|l|liter)$/i', $product_slug)) {
            abort(404);
        }

        // Find product by slug
        $args = [
            'name' => $product_slug,
            'post_type' => 'product',
            'post_status' => 'publish',
            'numberposts' => 1,
        ];
        $posts = get_posts($args);

        if (empty($posts)) {
            abort(404);
        }

        $this->productId = $posts[0]->ID;
        $this->product = Product::find($this->productId);

        if (! $this->product) {
            abort(404);
        }

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
        return view('livewire.product-review-page', [
            'title' => sprintf(__('Schrijf een review voor %s', 'sage'), $this->product->name),
        ]);
    }
}
