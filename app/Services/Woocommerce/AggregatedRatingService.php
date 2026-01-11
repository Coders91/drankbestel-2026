<?php

namespace App\Services\Woocommerce;

/**
 * Service to sync aggregated ratings to WooCommerce's product_visibility taxonomy.
 *
 * When products share the same name (size variants), this service ensures
 * all variants have the same rating in the product_visibility taxonomy,
 * based on the aggregated rating from all reviews across variants.
 */
class AggregatedRatingService
{
    /**
     * Register hooks for rating synchronization.
     */
    public static function register(): void
    {
        // New review posted
        add_action('comment_post', [self::class, 'onNewReview'], 10, 3);

        // Review edited (rating changed)
        add_action('edit_comment', [self::class, 'onReviewEdited'], 10, 2);

        // Any status transition (approved/unapproved/spam/trash)
        add_action('transition_comment_status', [self::class, 'onReviewStatusTransition'], 10, 3);

        // Permanent deletion
        add_action('deleted_comment', [self::class, 'onReviewDeleted'], 10, 2);
    }

    /**
     * Handle new review submission.
     * Hook: comment_post (int $comment_ID, int|string $comment_approved, array $commentdata)
     */
    public static function onNewReview(int $commentId, $commentApproved, array $commentData): void
    {
        if (($commentData['comment_type'] ?? '') !== 'review') {
            return;
        }

        $productId = (int) ($commentData['comment_post_ID'] ?? 0);
        if ($productId) {
            self::syncAggregatedRating($productId);
        }
    }

    /**
     * Handle review edit.
     * Hook: edit_comment (int $comment_ID, array $data)
     */
    public static function onReviewEdited(int $commentId, array $data): void
    {
        $comment = get_comment($commentId);
        if (!$comment || $comment->comment_type !== 'review') {
            return;
        }

        self::syncAggregatedRating((int) $comment->comment_post_ID);
    }

    /**
     * Handle any review status transition.
     * Hook: transition_comment_status (string $new_status, string $old_status, WP_Comment $comment)
     */
    public static function onReviewStatusTransition(string $newStatus, string $oldStatus, \WP_Comment $comment): void
    {
        if ($comment->comment_type !== 'review') {
            return;
        }

        self::syncAggregatedRating((int) $comment->comment_post_ID);
    }

    /**
     * Handle review deletion.
     * Hook: deleted_comment (int $comment_id, WP_Comment $comment)
     */
    public static function onReviewDeleted(int $commentId, \WP_Comment $comment): void
    {
        if ($comment->comment_type !== 'review') {
            return;
        }

        self::syncAggregatedRating((int) $comment->comment_post_ID);
    }

    /**
     * Sync aggregated rating to product_visibility taxonomy for all products with the same name.
     */
    public static function syncAggregatedRating(int $productId): void
    {
        $product = wc_get_product($productId);
        if (!$product) {
            return;
        }

        $productName = $product->get_name();

        // Find all products with the same name
        $relatedProductIds = self::getProductIdsByName($productName);

        if (empty($relatedProductIds)) {
            return;
        }

        // Calculate aggregated rating
        $aggregatedRating = self::calculateAggregatedRating($relatedProductIds);

        // Determine which rating term to use (rounded to nearest integer)
        $ratingTerm = $aggregatedRating > 0 ? 'rated-' . round($aggregatedRating) : null;

        // Update all related products with the aggregated rating
        foreach ($relatedProductIds as $relatedId) {
            self::updateProductRatingTerms((int) $relatedId, $ratingTerm);
        }
    }

    /**
     * Get all product IDs with the same name.
     */
    protected static function getProductIdsByName(string $productName): array
    {
        global $wpdb;

        return $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts
             WHERE post_title = %s
             AND post_type = 'product'
             AND post_status = 'publish'",
            $productName
        ));
    }

    /**
     * Calculate aggregated rating from all reviews across given product IDs.
     */
    protected static function calculateAggregatedRating(array $productIds): float
    {
        $reviews = get_comments([
            'post__in' => $productIds,
            'status' => 'approve',
            'type' => 'review',
        ]);

        if (empty($reviews)) {
            return 0.0;
        }

        $totalRating = 0;
        $reviewCount = count($reviews);

        foreach ($reviews as $review) {
            $totalRating += (int) get_comment_meta($review->comment_ID, 'rating', true);
        }

        return $totalRating / $reviewCount;
    }

    /**
     * Update the product_visibility taxonomy terms for a product's rating.
     */
    protected static function updateProductRatingTerms(int $productId, ?string $newRatingTerm): void
    {
        // All possible rating terms
        $ratingTerms = ['rated-1', 'rated-2', 'rated-3', 'rated-4', 'rated-5'];

        // Remove all existing rating terms
        wp_remove_object_terms($productId, $ratingTerms, 'product_visibility');

        // Add the new rating term if we have one
        if ($newRatingTerm && in_array($newRatingTerm, $ratingTerms, true)) {
            wp_set_object_terms($productId, $newRatingTerm, 'product_visibility', true);
        }

        // Clear product cache
        wc_delete_product_transients($productId);

        // Update post to trigger Filter Everything cache refresh
        $post = get_post($productId);
        if ($post) {
            wp_update_post($post);
        }
    }

    /**
     * Sync all products (useful for initial setup or bulk sync).
     * Can be called via WP-CLI or admin action.
     */
    public static function syncAllProducts(): int
    {
        global $wpdb;

        // Get all unique product names that have reviews
        $productNames = $wpdb->get_col(
            "SELECT DISTINCT p.post_title
             FROM $wpdb->posts p
             INNER JOIN $wpdb->comments c ON p.ID = c.comment_post_ID
             WHERE p.post_type = 'product'
             AND p.post_status = 'publish'
             AND c.comment_type = 'review'
             AND c.comment_approved = '1'"
        );

        $synced = 0;

        foreach ($productNames as $productName) {
            $productIds = self::getProductIdsByName($productName);

            if (empty($productIds)) {
                continue;
            }

            $aggregatedRating = self::calculateAggregatedRating($productIds);
            $ratingTerm = $aggregatedRating > 0 ? 'rated-' . round($aggregatedRating) : null;

            foreach ($productIds as $productId) {
                self::updateProductRatingTerms((int) $productId, $ratingTerm);
                $synced++;
            }
        }

        return $synced;
    }
}
