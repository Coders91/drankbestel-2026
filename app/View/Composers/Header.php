<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Services\MegaMenuService;
use Roots\Acorn\View\Composer;

class Header extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'sections.header',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        $menuService = app(MegaMenuService::class);

        $allCategories = $menuService->getCategoriesForMegaPanel();
        $spiritsCategories = $menuService->getCategoriesForMegaPanel('sterke-drank');

        if ($spiritsCategories->isEmpty()) {
            $spiritsCategories = $allCategories;
        }

        return [
            'megaMenuCategories' => $allCategories,
            'spiritsCategories' => $spiritsCategories,
            'featuredBrands' => $menuService->getFeaturedBrands(6),
        ];
    }
}
