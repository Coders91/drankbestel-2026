<?php

namespace App\Providers;

use App\Services\StructuredData\Builders\ArticleBuilder;
use App\Services\StructuredData\Builders\BreadcrumbBuilder;
use App\Services\StructuredData\Builders\ItemListBuilder;
use App\Services\StructuredData\Builders\OrganizationBuilder;
use App\Services\StructuredData\Builders\ProductBuilder;
use App\Services\StructuredData\Builders\RecipeBuilder;
use App\Services\StructuredData\Builders\WebPageBuilder;
use App\Services\StructuredData\Builders\WebSiteBuilder;
use App\Services\StructuredData\StructuredDataService;
use Illuminate\Support\ServiceProvider;

class StructuredDataServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register builders as singletons
        $this->app->singleton(OrganizationBuilder::class);
        $this->app->singleton(WebSiteBuilder::class);
        $this->app->singleton(WebPageBuilder::class);
        $this->app->singleton(BreadcrumbBuilder::class);
        $this->app->singleton(ProductBuilder::class);
        $this->app->singleton(ItemListBuilder::class);
        $this->app->singleton(ArticleBuilder::class);
        $this->app->singleton(RecipeBuilder::class);

        // Register main service
        $this->app->singleton(StructuredDataService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
