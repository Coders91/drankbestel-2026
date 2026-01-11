<?php

namespace App\View\Composers;

use App\Services\StructuredData\StructuredDataService;
use Roots\Acorn\View\Composer;

class StructuredData extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'partials.head',
    ];

    public function __construct(
        protected StructuredDataService $structuredDataService
    ) {}

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'structuredData' => $this->structuredDataService->build(),
        ];
    }
}
