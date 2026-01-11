<?php

namespace App\Services\StructuredData\Builders;

use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use App\View\Components\Breadcrumbs;
use Spatie\SchemaOrg\BreadcrumbList;
use Spatie\SchemaOrg\Schema;

class BreadcrumbBuilder
{
    use HasSchemaIdentifiers;

    public function build(): ?BreadcrumbList
    {
        $breadcrumbComponent = new Breadcrumbs();
        $crumbs = $breadcrumbComponent->crumbs;

        if (empty($crumbs)) {
            return null;
        }

        $listItems = [];
        $position = 1;

        foreach ($crumbs as $crumb) {
            $name = $crumb['name'] ?? '';

            // Replace SVG icon with "Home" text
            if (str_contains($name, '<svg')) {
                $name = __('Home', 'sage');
            }

            $url = $crumb['url'] ?? $this->getCurrentUrl();

            // Build ListItem with proper structure for Google
            $listItem = Schema::listItem()
                ->position($position)
                ->name($name)
                ->item($url);

            $listItems[] = $listItem;
            $position++;
        }

        if (empty($listItems)) {
            return null;
        }

        return Schema::breadcrumbList()
            ->setProperty('@id', $this->breadcrumbId())
            ->itemListElement($listItems);
    }
}
