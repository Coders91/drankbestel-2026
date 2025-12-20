<?php

namespace App\Services\Lightspeed\Sync;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StockSyncStrategy implements SyncStrategyInterface
{
    public function getName(): string
    {
        return 'stock';
    }

    public function getDirection(): string
    {
        return 'ls_to_wc';
    }

    public function getRequiredRelations(): array
    {
        return ['ItemShops'];
    }

    public function shouldSync(array $cachedItem, array $lightspeedItem): bool
    {
        $lightspeedStock = $this->extractStock($lightspeedItem);

        if ($lightspeedStock === null) {
            return false;
        }

        $cachedStock = $cachedItem['stock'] ?? null;

        return $cachedStock !== $lightspeedStock;
    }

    public function sync(Collection $lightspeedItems, array $cachedProducts): SyncResult
    {
        $processed = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $errorMessages = [];

        // Build reverse lookup: lightspeed_id => product_id
        $lightspeedToProduct = [];
        foreach ($cachedProducts as $productId => $data) {
            if (isset($data['lightspeed_id'])) {
                $lightspeedToProduct[$data['lightspeed_id']] = $productId;
            }
        }

        foreach ($lightspeedItems as $item) {
            $processed++;

            try {
                $lightspeedId = $item['itemID'] ?? null;

                if (! $lightspeedId) {
                    $skipped++;
                    continue;
                }

                // Check if we have this product in our cache
                $productId = $lightspeedToProduct[$lightspeedId] ?? null;

                if (! $productId) {
                    $skipped++;
                    continue;
                }

                $cachedItem = $cachedProducts[$productId] ?? [];
                $lightspeedStock = $this->extractStock($item);

                if ($lightspeedStock === null) {
                    $skipped++;
                    continue;
                }

                // Only update if stock differs
                if (! $this->shouldSync($cachedItem, $item)) {
                    $skipped++;
                    continue;
                }

                // Update WooCommerce stock
                if ($this->updateWooCommerceStock($productId, $lightspeedStock)) {
                    $updated++;
                    Log::debug("Stock updated for product {$productId}: {$lightspeedStock}");
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                $errorMessages[] = "Error processing item: " . $e->getMessage();
                Log::error("Stock sync error: " . $e->getMessage());
            }
        }

        return new SyncResult(
            processed: $processed,
            updated: $updated,
            skipped: $skipped,
            errors: $errors,
            errorMessages: $errorMessages
        );
    }

    /**
     * Extract stock quantity from Lightspeed item
     */
    private function extractStock(array $lightspeedItem): ?int
    {
        // ItemShops can be an array of shops or a single shop
        $itemShops = $lightspeedItem['ItemShops']['ItemShop'] ?? null;

        if (! $itemShops) {
            return null;
        }

        // Handle both single shop and multiple shops
        $shop = is_array($itemShops) && isset($itemShops[0])
            ? $itemShops[0]
            : $itemShops;

        if (! isset($shop['qoh'])) {
            return null;
        }

        $qty = (int) $shop['qoh'];

        // Ensure non-negative stock
        return max(0, $qty);
    }

    /**
     * Update WooCommerce product stock
     */
    private function updateWooCommerceStock(int $productId, int $quantity): bool
    {
        $product = wc_get_product($productId);

        if (! $product) {
            return false;
        }

        // Set sync guard to prevent cache update hooks from firing
        if (! defined('LIGHTSPEED_SYNC_IN_PROGRESS')) {
            define('LIGHTSPEED_SYNC_IN_PROGRESS', true);
        }

        wc_update_product_stock($product, $quantity);

        return true;
    }
}
