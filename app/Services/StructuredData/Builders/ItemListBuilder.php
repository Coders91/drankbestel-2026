<?php

namespace App\Services\StructuredData\Builders;

use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use App\View\Models\Product;
use Illuminate\Support\Collection;
use Spatie\SchemaOrg\ItemList;
use Spatie\SchemaOrg\Schema;

class ItemListBuilder
{
    use HasSchemaIdentifiers;

    public function build(Collection $products): ItemList
    {
        $listItems = [];
        $position = 1;

        foreach ($products as $product) {
            if (! $product instanceof Product) {
                continue;
            }

            $listItems[] = Schema::listItem()
                ->position($position)
                ->url($product->url)
                ->name($product->title);

            $position++;
        }

        $itemList = Schema::itemList()
            ->itemListElement($listItems)
            ->numberOfItems(count($listItems));

        // Add list name based on context
        if (is_product_category()) {
            $term = get_queried_object();
            $itemList->name($term->name ?? __('Producten', 'sage'));
        } elseif (is_product_tag()) {
            $term = get_queried_object();
            $itemList->name($term->name ?? __('Producten', 'sage'));
        } elseif (is_shop()) {
            $itemList->name(__('Alle producten', 'sage'));
        } else {
            $itemList->name(__('Producten', 'sage'));
        }

        return $itemList;
    }
}
