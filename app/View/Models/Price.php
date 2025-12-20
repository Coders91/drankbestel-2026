<?php

namespace App\View\Models;

use App\Support\Money;

readonly class Price
{
    public function __construct(
        public Money $amount,
        public bool $is_free = false,
    ) {}

    public static function from(string|int|float|null $value): self
    {
        $money = Money::from($value);

        return new self(
            amount: $money,
            is_free: $money->amount === 0,
        );
    }

    public function formatted(): string
    {
        if ($this->is_free) {
            return __('Gratis', 'sage');
        }

        return $this->amount->formatted();
    }

    public function display(): string
    {
        return $this->is_free
            ? __('Gratis', 'text-domain')
            : $this->amount->formatted();
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
