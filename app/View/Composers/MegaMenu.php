<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Services\MegaMenuService;
use Roots\Acorn\View\Composer;

class MegaMenu extends Composer
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
     * The mega menu service.
     */
    protected MegaMenuService $menuService;

    /**
     * Create a new composer instance.
     */
    public function __construct(MegaMenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Data to be passed to view before rendering.
     *
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'megaMenuCategories' => $this->menuService->getCategoriesForMegaPanel(),
            'featuredBrands' => $this->menuService->getFeaturedBrands(6),
        ];
    }
}
