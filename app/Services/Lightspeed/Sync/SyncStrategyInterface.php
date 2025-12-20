<?php

namespace App\Services\Lightspeed\Sync;

use Illuminate\Support\Collection;

interface SyncStrategyInterface
{
    /**
     * Get the strategy name for display/logging
     */
    public function getName(): string;

    /**
     * Get sync direction: 'ls_to_wc' (Lightspeed to WooCommerce) or 'wc_to_ls'
     */
    public function getDirection(): string;

    /**
     * Get the Lightspeed API relations needed for this sync
     * e.g., ['ItemShops'] for stock, ['Prices', 'Images'] for full product
     */
    public function getRequiredRelations(): array;

    /**
     * Determine if sync is needed based on cached vs API data
     */
    public function shouldSync(array $cachedItem, array $lightspeedItem): bool;

    /**
     * Perform the sync for a batch of items
     *
     * @param  Collection  $lightspeedItems  Items from Lightspeed API
     * @param  array  $cachedProducts  Current cached product mappings
     * @return SyncResult Result containing counts and any errors
     */
    public function sync(Collection $lightspeedItems, array $cachedProducts): SyncResult;
}
