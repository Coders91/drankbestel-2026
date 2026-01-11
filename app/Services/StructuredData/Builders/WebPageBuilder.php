<?php

namespace App\Services\StructuredData\Builders;

use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;

class WebPageBuilder
{
    use HasSchemaIdentifiers;

    public function build(
        string $pageType = 'WebPage',
        string $name = '',
        string $description = '',
        ?string $mainEntityId = null,
    ): BaseType {
        // Create appropriate schema type
        $webPage = match ($pageType) {
            'ItemPage' => Schema::itemPage(),
            'CollectionPage' => Schema::collectionPage(),
            'FAQPage' => Schema::fAQPage(),
            'AboutPage' => Schema::aboutPage(),
            'ContactPage' => Schema::contactPage(),
            default => Schema::webPage(),
        };

        $url = $this->getCurrentUrl();

        $webPage
            ->setProperty('@id', $this->webPageId($url))
            ->url($url)
            ->name($name ?: get_bloginfo('name'))
            ->isPartOf(['@id' => $this->websiteId()])
            ->inLanguage('nl-NL');

        if ($description) {
            $webPage->description($description);
        }

        // Set mainEntity for product pages
        if ($mainEntityId) {
            $webPage->mainEntity(['@id' => $mainEntityId]);
        }

        // Date information for singular posts/pages
        if (is_singular()) {
            $webPage->dateModified(get_the_modified_date('c'));
            $webPage->datePublished(get_the_date('c'));
        }

        // Featured image
        $thumbnailId = get_post_thumbnail_id();
        if ($thumbnailId) {
            $imageUrl = wp_get_attachment_image_url($thumbnailId, 'large');
            if ($imageUrl) {
                $webPage->primaryImageOfPage(
                    Schema::imageObject()->url($imageUrl)
                );
            }
        }

        // Reference to breadcrumbs (will be on the page if exists)
        $webPage->breadcrumb(['@id' => $this->breadcrumbId($url)]);

        return $webPage;
    }
}
