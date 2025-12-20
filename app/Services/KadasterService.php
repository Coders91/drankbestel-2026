<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Exception;

class KadasterService
{
    protected string $baseUrl = 'https://api.bag.kadaster.nl/lvbag/individuelebevragingen/v2';
    protected int $timeout = 30;
    protected int $pageSize = 20;

    /**
     * Validate an address using the Kadaster BAG API
     *
     * @param string $postcode
     * @param int $huisnummer
     * @param string|null $huisletter
     * @param string|null $huisnummertoevoeging
     * @param bool $exacteMatch
     * @return Collection
     * @throws Exception
     */
    public function validateAddress(
        string $postcode,
        int $huisnummer,
        ?string $huisletter = null,
        ?string $huisnummertoevoeging = null,
        bool $exacteMatch = true
    ): Collection {
        $params = [
            'postcode' => strtoupper(str_replace(' ', '', $postcode)),
            'huisnummer' => $huisnummer,
            'exacteMatch' => $exacteMatch,
            'page' => 1,
            'pageSize' => 10,
            'inclusiefEindStatus' => 'true'
        ];

        if ($huisletter) {
            $params['huisletter'] = strtoupper($huisletter);
        }

        if ($huisnummertoevoeging) {
            $params['huisnummertoevoeging'] = $huisnummertoevoeging;
        }

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'X-Api-Key' => env('BAG_KADASTER_API_KEY')
            ])
            ->get($this->baseUrl . '/adressen', $params);

        if (!$response->successful()) {
            throw new Exception("BAG API request failed: " . $response->status() . " - " . $response->body());
        }

        $data = $response->json();

        return collect($data['_embedded']['adressen'] ?? [])
            ->map(fn($address) => $this->formatAddress($address));
    }

    /**
     * Format address data from API response
     *
     * @param array $address
     * @return array
     */
    protected function formatAddress(array $address): array
    {
        return [
            'street_name' => $address['openbareRuimteNaam'] ?? null,
            'short_name' => $address['korteNaam'] ?? null,
            'house_number' => $address['huisnummer'] ?? null,
            'house_letter' => $address['huisletter'] ?? null,
            'house_number_addition' => $address['huisnummertoevoeging'] ?? null,
            'postcode' => $address['postcode'] ?? null,
            'city' => $address['woonplaatsNaam'] ?? null,
            'address_line_1' => $address['adresregel5'] ?? null,
            'address_line_2' => $address['adresregel6'] ?? null,
            'nummeraanduiding_id' => $address['nummeraanduidingIdentificatie'] ?? null,
            'openbare_ruimte_id' => $address['openbareRuimteIdentificatie'] ?? null,
            'woonplaats_id' => $address['woonplaatsIdentificatie'] ?? null,
            'adresseerbaar_object_id' => $address['adresseerbaarObjectIdentificatie'] ?? null,
            'pand_ids' => $address['pandIdentificaties'] ?? [],
            'links' => $address['_links'] ?? []
        ];
    }

    /**
     * Set custom timeout for API requests
     *
     * @param int $timeout
     * @return self
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Set page size for API requests
     *
     * @param int $pageSize
     * @return self
     */
    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;
        return $this;
    }
}
