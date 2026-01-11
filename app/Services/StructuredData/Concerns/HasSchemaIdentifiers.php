<?php

namespace App\Services\StructuredData\Concerns;

trait HasSchemaIdentifiers
{
    protected function siteUrl(): string
    {
        return trailingslashit(home_url());
    }

    protected function organizationId(): string
    {
        return $this->siteUrl() . '#organization';
    }

    protected function websiteId(): string
    {
        return $this->siteUrl() . '#website';
    }

    protected function webPageId(?string $url = null): string
    {
        $url = $url ?? $this->getCurrentUrl();

        return trailingslashit($url) . '#webpage';
    }

    protected function breadcrumbId(?string $url = null): string
    {
        $url = $url ?? $this->getCurrentUrl();

        return trailingslashit($url) . '#breadcrumb';
    }

    protected function productId(int $productId): string
    {
        return trailingslashit(get_permalink($productId)) . '#product';
    }

    protected function productGroupId(int $productId): string
    {
        return trailingslashit(get_permalink($productId)) . '#productgroup';
    }

    protected function offerId(int $productId, ?int $index = null): string
    {
        $base = trailingslashit(get_permalink($productId)) . '#offer';

        return $index !== null ? "{$base}-{$index}" : $base;
    }

    protected function getCurrentUrl(): string
    {
        if (is_singular()) {
            return get_permalink() ?: $this->siteUrl();
        }

        if (is_tax() || is_category() || is_tag()) {
            return get_term_link(get_queried_object()) ?: $this->siteUrl();
        }

        if (is_post_type_archive()) {
            return get_post_type_archive_link(get_post_type()) ?: $this->siteUrl();
        }

        if (is_shop()) {
            return get_permalink(wc_get_page_id('shop')) ?: $this->siteUrl();
        }

        return $this->siteUrl();
    }
}
