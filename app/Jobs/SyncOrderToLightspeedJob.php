<?php

namespace App\Jobs;

use App\Services\Lightspeed\LightspeedOrderSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncOrderToLightspeedJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $orderId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LightspeedOrderSyncService $orderSyncService): void
    {
        Log::info("SyncOrderToLightspeedJob started for Order #{$this->orderId}");

        try {
            $orderSyncService->syncOrderToLightspeed($this->orderId);

            Log::info("SyncOrderToLightspeedJob completed for Order #{$this->orderId}");
        } catch (\Exception $e) {
            Log::error("SyncOrderToLightspeedJob failed for Order #{$this->orderId}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SyncOrderToLightspeedJob failed after {$this->tries} attempts for Order #{$this->orderId}", [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
