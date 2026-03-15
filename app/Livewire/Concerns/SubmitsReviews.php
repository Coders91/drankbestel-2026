<?php

namespace App\Livewire\Concerns;

trait SubmitsReviews
{
    protected function submitReview(): bool
    {
        $this->form->validate();

        $product = wc_get_product($this->productId);

        if (! $product) {
            $this->addError('form', __('Product niet gevonden.', 'sage'));

            return false;
        }

        $commentData = [
            'comment_post_ID' => $this->productId,
            'comment_author' => sanitize_text_field($this->form->author),
            'comment_author_email' => sanitize_email($this->form->email),
            'comment_content' => sanitize_textarea_field($this->form->content),
            'comment_type' => 'review',
            'comment_parent' => 0,
            'user_id' => get_current_user_id(),
            'comment_approved' => 0, // Pending moderation
        ];

        $commentId = wp_insert_comment($commentData);

        if ($commentId) {
            // Add rating meta (WooCommerce stores ratings in comment meta)
            update_comment_meta($commentId, 'rating', $this->form->rating);

            // Mark as verified if user purchased the product
            if ($this->isLoggedIn && wc_customer_bought_product(
                $this->form->email,
                get_current_user_id(),
                $this->productId
            )) {
                update_comment_meta($commentId, 'verified', 1);
            }

            return true;
        }

        $this->addError('form', __('Er is iets misgegaan bij het plaatsen van je review.', 'sage'));

        return false;
    }
}
