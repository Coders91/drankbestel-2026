<?php

namespace App\Services\StructuredData\Builders;

use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use Illuminate\Support\Facades\Vite;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\OnlineStore;

class OrganizationBuilder
{
    use HasSchemaIdentifiers;

    public function build(): OnlineStore
    {
        $config = config('store');

        $returnsConfig = config('store.returns', [
            'days' => 30,
            'country' => 'NL',
            'method' => 'ReturnByMail',
            'fees' => 'FreeReturn',
            'link' => 'https://drankbestel.nl/klantenservice/ruilen-retourneren/',
        ]);

        return Schema::onlineStore()
            ->name($config['details']['name'])
            ->description($config['details']['description'] ?? null)
            ->url($this->siteUrl())
            ->logo(Vite::asset('resources/images/logos/drankbestel.svg'))
            ->address(
                Schema::postalAddress()
                    ->addressLocality($config['address']['city'])
                    ->addressCountry($config['address']['country'])
                    ->postalCode($config['address']['zipcode'])
                    ->streetAddress($config['address']['street'])
            )
            ->sameAs($config['social'] ?? [])
            ->legalName($config['details']['legal_name'])
            ->vatID($config['details']['btw'])
            ->areaServed($config['shipping']['destinations'] ?? 'NL')
            ->founder(
                Schema::organization()->identifier($this->organizationId())
            )
            ->contactPoint(
                Schema::contactPoint()
                    ->telephone($config['contact']['phone'])
                    ->email($config['contact']['email'])
                    ->contactType('Klantenservice')
                    ->areaServed('NL')
            )
            ->hasMerchantReturnPolicy(
                Schema::merchantReturnPolicy()
                    ->applicableCountry($returnsConfig['country'])
                    ->returnPolicyCountry($returnsConfig['country'])
                    ->returnPolicyCategory('https://schema.org/MerchantReturnFiniteReturnWindow')
                    ->merchantReturnDays($returnsConfig['days'])
                    ->merchantReturnLink($returnsConfig['link'])
                    ->itemCondition('https://schema.org/NewCondition')
                    ->returnMethod('https://schema.org/' . $returnsConfig['method'])
                    ->returnFees('https://schema.org/' . $returnsConfig['fees'])
                    ->refundType('https://schema.org/FullRefund')
            )
            ->mainEntityOfPage(
                Schema::webPage()->identifier($this->siteUrl() . '#onlinestore')
            );
    }
}
