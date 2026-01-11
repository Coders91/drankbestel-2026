<?php

namespace App\Services\StructuredData\Builders;

use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\WebSite;

class WebSiteBuilder
{
    use HasSchemaIdentifiers;

    public function build(bool $includePublisher = false): WebSite
    {
        $webSite = Schema::webSite()
            ->setProperty('@id', $this->websiteId())
            ->url($this->siteUrl())
            ->name(config('store.details.name'))
            ->description(get_bloginfo('description'))
            ->inLanguage('nl-NL');

        if ($includePublisher) {
            $webSite->publisher(['@id' => $this->organizationId()]);
        }

        return $webSite;
    }
}
