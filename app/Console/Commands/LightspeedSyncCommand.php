<?php

namespace App\Console\Commands;

use App\Services\Lightspeed\LightspeedSyncService;
use Illuminate\Console\Command;

class LightspeedSyncCommand extends Command
{
    protected $signature = 'lightspeed:sync
                            {--type=stock : Sync strategy to use}
                            {--refresh-cache : Force cache refresh before sync}
                            {--dry-run : Show what would be synced without making changes}
                            {--limit=0 : Maximum number of products to sync (0 = unlimited)}';

    protected $description = 'Sync product stock between Lightspeed Retail and WooCommerce';

    public function handle(LightspeedSyncService $syncService): int
    {
        $strategyName = $this->option('type');
        $refreshCache = $this->option('refresh-cache');
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $availableStrategies = $syncService->getAvailableStrategies();

        if (! in_array($strategyName, $availableStrategies)) {
            $this->error("Unknown strategy \"{$strategyName}\". Available: " . implode(', ', $availableStrategies));

            return Command::FAILURE;
        }

        $this->info("Starting Lightspeed sync...");
        $this->table(['Option', 'Value'], [
            ['Strategy', $strategyName],
            ['Refresh cache', $refreshCache ? 'yes' : 'no'],
            ['Dry run', $dryRun ? 'yes' : 'no'],
            ['Limit', $limit > 0 ? $limit : 'unlimited'],
        ]);
        $this->newLine();

        $result = $syncService->sync(
            strategyName: $strategyName,
            refreshCache: $refreshCache,
            dryRun: $dryRun,
            limit: $limit,
            progressCallback: fn (string $message) => $this->line($message),
        );

        $this->newLine();

        if ($result->isSuccessful()) {
            $this->info('Sync complete!');
        } else {
            $this->warn('Sync completed with errors.');
        }

        $this->table(['Metric', 'Count'], [
            ['Processed', $result->processed],
            ['Updated', $result->updated],
            ['Skipped', $result->skipped],
            ['Errors', $result->errors],
        ]);

        if (! empty($result->errorMessages)) {
            $this->newLine();
            $this->error('Error details:');
            foreach ($result->errorMessages as $error) {
                $this->line("  - {$error}");
            }
        }

        return $result->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}
