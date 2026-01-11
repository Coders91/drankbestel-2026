<?php

namespace App\Console\Commands;

use App\Services\Woocommerce\AggregatedRatingService;
use Illuminate\Console\Command;

class SyncAggregatedRatingsCommand extends Command
{
    protected $signature = 'ratings:sync';

    protected $description = 'Sync aggregated ratings to product_visibility taxonomy for all products';

    public function handle(): int
    {
        $this->info('Syncing aggregated ratings for all products...');
        $this->newLine();

        $synced = AggregatedRatingService::syncAllProducts();

        $this->info("Successfully synced ratings for {$synced} products.");

        return Command::SUCCESS;
    }
}
