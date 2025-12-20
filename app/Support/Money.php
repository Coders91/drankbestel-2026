<?php

namespace App\Support;

use NumberFormatter;

readonly class Money
{
    public function __construct(
        public int $amount,      // stored in cents
        public string $currency, // e.g. EUR
    ) {}

    /**
     * Create from a WooCommerce price (string or numeric)
     */
    public static function from(string|int|float|null $value): self
    {
        $value = $value ?: '0';

        $amount = (int) round(((float) $value) * 100);

        return new self(
            amount: $amount,
            currency: get_woocommerce_currency(),
        );
    }

    /**
     * Human-readable output
     */
    public function formatted(): string
    {
        $formatter = new NumberFormatter(
            get_locale(),
            NumberFormatter::CURRENCY
        );

        return $formatter->formatCurrency(
            $this->amount / 100,
            $this->currency
        );
    }

    /**
     * Raw decimal value (use sparingly)
     */
    public function decimal(): float
    {
        return $this->amount / 100;
    }

    /**
     * Comparisons
     */
    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}
