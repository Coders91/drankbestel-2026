<?php

namespace App\View\Models;

readonly class ProductAttribute
{
    public function __construct(
        public string  $key,
        public string  $label,
        public string  $value,
        public ?string $url = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'label' => $this->label,
            'value' => $this->value,
            'url'   => $this->url,
        ]);
    }
}
