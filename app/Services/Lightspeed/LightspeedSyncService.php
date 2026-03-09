<?php

namespace App\Services\Lightspeed;

use App\Services\Lightspeed\Sync\SyncResult;
use App\Services\Lightspeed\Sync\SyncStrategyInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use TimothyDC\LightspeedRetailApi\Facades\LightspeedRetailApi;

class LightspeedSyncService
{
    private const BATCH_SIZE = 100;

    /**
     * @var array<string, SyncStrategyInterface>
     */
    private array $strategies = [];

    public function __construct(
        private ProductMappingCache $cache
    ) {}

    /**
     * Register a sync strategy
     */
    public function registerStrategy(SyncStrategyInterface $strategy): void
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    /**
     * Get available strategy names
     */
    public function getAvailableStrategies(): array
    {
        return array_keys($this->strategies);
    }

    /**
     * Get the cache service
     */
    public function getCache(): ProductMappingCache
    {
        return $this->cache;
    }

    /**
     * Run sync with a specific strategy
     */
    public function sync(
        string $strategyName,
        bool $refreshCache = false,
        bool $dryRun = false,
        int $limit = 0,
        ?callable $progressCallback = null
    ): SyncResult {
        $strategy = $this->strategies[$strategyName] ?? null;

        if (! $strategy) {
            throw new InvalidArgumentException("Unknown sync strategy: {$strategyName}");
        }

        Log::info("Starting Lightspeed sync with strategy: {$strategyName}");

        // Refresh cache if requested
        if ($refreshCache) {
            $this->logProgress($progressCallback, 'Refreshing product cache...');
            $this->cache->refresh();
        }

        // Get cached products
        $cachedProducts = $this->cache->all();

        if (empty($cachedProducts)) {
            $this->logProgress($progressCallback, 'No products with Lightspeed IDs found in cache.');

            return new SyncResult();
        }

        // Get Lightspeed IDs to sync
        $lightspeedIds = $this->cache->getLightspeedIds()->values()->toArray();

        if ($limit > 0) {
            $lightspeedIds = array_slice($lightspeedIds, 0, $limit);
        }

        $totalProducts = count($lightspeedIds);
        $this->logProgress($progressCallback, "Found {$totalProducts} products to sync.");

        if ($dryRun) {
            $this->logProgress($progressCallback, 'Dry run mode - no changes will be made.');
        }

        // Process in batches
        $chunks = array_chunk($lightspeedIds, self::BATCH_SIZE);
        $totalChunks = count($chunks);
        $aggregateResult = new SyncResult();

        foreach ($chunks as $index => $chunk) {
            $chunkNumber = $index + 1;
            $this->logProgress(
                $progressCallback,
                "Processing batch {$chunkNumber}/{$totalChunks} (" . count($chunk) . " items)..."
            );

            try {
                // Fetch items from Lightspeed API
                $items = $this->fetchFromLightspeed($chunk, $strategy->getRequiredRelations());

                if ($dryRun) {
                    // In dry run, just count what would be updated
                    $result = $this->dryRunBatch($items, $cachedProducts, $strategy);
                } else {
                    // Actually sync the items
                    $result = $strategy->sync(collect($items), $cachedProducts);
                }

                $aggregateResult = $aggregateResult->merge($result);

                $this->logProgress(
                    $progressCallback,
                    "Batch {$chunkNumber}: {$result->getSummary()}"
                );
            } catch (Exception $e) {
                Log::error("Error processing batch {$chunkNumber}: " . $e->getMessage());

                $aggregateResult = $aggregateResult->merge(new SyncResult(
                    processed: count($chunk),
                    errors: count($chunk),
                    errorMessages: ["Batch {$chunkNumber} failed: " . $e->getMessage()]
                ));
            }
        }

        // Refresh cache after sync to update stock values
        if (! $dryRun) {
            $this->logProgress($progressCallback, 'Refreshing cache with updated values...');
            $this->cache->refresh();
        }

        Log::info("Lightspeed sync completed: {$aggregateResult->getSummary()}");
        $this->logProgress($progressCallback, "Sync completed: {$aggregateResult->getSummary()}");

        return $aggregateResult;
    }

    /**
     * Fetch items from Lightspeed API
     */
    private function fetchFromLightspeed(array $itemIds, array $relations): array
    {
        if (empty($itemIds)) {
            return [];
        }

        $response = LightspeedRetailApi::api()->Item()->get(null, [
            'itemID' => 'IN,' . json_encode($itemIds),
            'load_relations' => $relations,
        ]);

        if ($response instanceof \Illuminate\Support\Collection) {
            return $response->toArray();
        }

        return is_array($response) ? $response : [];
    }

    /**
     * Perform a dry run for a batch
     */
    private function dryRunBatch(array $items, array $cachedProducts, SyncStrategyInterface $strategy): SyncResult
    {
        $processed = 0;
        $wouldUpdate = 0;
        $skipped = 0;

        // Build reverse lookup
        $lightspeedToProduct = [];
        foreach ($cachedProducts as $productId => $data) {
            if (isset($data['lightspeed_id'])) {
                $lightspeedToProduct[$data['lightspeed_id']] = $productId;
            }
        }

        foreach ($items as $item) {
            $processed++;

            $lightspeedId = $item['itemID'] ?? null;

            if (! $lightspeedId) {
                $skipped++;
                continue;
            }

            $productId = $lightspeedToProduct[$lightspeedId] ?? null;

            if (! $productId) {
                $skipped++;
                continue;
            }

            $cachedItem = $cachedProducts[$productId] ?? [];

            if ($strategy->shouldSync($cachedItem, $item)) {
                $wouldUpdate++;
            } else {
                $skipped++;
            }
        }

        return new SyncResult(
            processed: $processed,
            updated: $wouldUpdate,
            skipped: $skipped
        );
    }

    /**
     * Log progress via callback
     */
    private function logProgress(?callable $callback, string $message): void
    {
        if ($callback) {
            $callback($message);
        }
    }
}
