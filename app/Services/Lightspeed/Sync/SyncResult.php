<?php

namespace App\Services\Lightspeed\Sync;

class SyncResult
{
    public function __construct(
        public readonly int $processed = 0,
        public readonly int $updated = 0,
        public readonly int $skipped = 0,
        public readonly int $errors = 0,
        public readonly array $errorMessages = [],
    ) {}

    /**
     * Merge another result into this one
     */
    public function merge(SyncResult $other): self
    {
        return new self(
            processed: $this->processed + $other->processed,
            updated: $this->updated + $other->updated,
            skipped: $this->skipped + $other->skipped,
            errors: $this->errors + $other->errors,
            errorMessages: array_merge($this->errorMessages, $other->errorMessages),
        );
    }

    /**
     * Check if sync completed without errors
     */
    public function isSuccessful(): bool
    {
        return $this->errors === 0;
    }

    /**
     * Get a summary string
     */
    public function getSummary(): string
    {
        return sprintf(
            'Processed: %d, Updated: %d, Skipped: %d, Errors: %d',
            $this->processed,
            $this->updated,
            $this->skipped,
            $this->errors
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'processed' => $this->processed,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'error_messages' => $this->errorMessages,
        ];
    }
}
