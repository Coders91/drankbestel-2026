<?php

namespace App\Jobs;

use App\Services\Lightspeed\LightspeedSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncLightspeedProductJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $strategyName = 'stock',
        private bool $refreshCache = false,
        private int $limit = 0
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LightspeedSyncService $syncService): void
    {
        Log::info("SyncLightspeedProductJob started with strategy: {$this->strategyName}");

        try {
            $result = $syncService->sync(
                strategyName: $this->strategyName,
                refreshCache: $this->refreshCache,
                limit: $this->limit,
                progressCallback: function (string $message) {
                    Log::debug("Lightspeed sync: {$message}");
                }
            );

            Log::info("SyncLightspeedProductJob completed: {$result->getSummary()}");

            if (! $result->isSuccessful()) {
                Log::warning('Lightspeed sync completed with errors', [
                    'errors' => $result->errorMessages,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("SyncLightspeedProductJob failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SyncLightspeedProductJob failed after {$this->tries} attempts", [
            'strategy' => $this->strategyName,
            'error' => $exception->getMessage(),
        ]);
    }
}
