<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailchimpService
{
    protected string $apiKey;

    protected string $listId;

    protected string $datacenter;

    public function __construct()
    {
        $this->apiKey = config('services.mailchimp.api_key', '');
        $this->listId = config('services.mailchimp.list_id', '');
        $this->datacenter = $this->extractDatacenter($this->apiKey);
    }

    /**
     * Extract datacenter from API key (e.g., 'us18' from 'xxx-us18')
     */
    protected function extractDatacenter(string $apiKey): string
    {
        $parts = explode('-', $apiKey);

        return end($parts) ?: 'us1';
    }

    /**
     * Get the base URL for the Mailchimp API
     */
    protected function getBaseUrl(): string
    {
        return sprintf('https://%s.api.mailchimp.com/3.0', $this->datacenter);
    }

    /**
     * Subscribe an email address to the list
     */
    public function subscribe(string $email, array $mergeFields = []): bool
    {
        if (empty($this->apiKey) || empty($this->listId)) {
            Log::warning('Mailchimp: API key or list ID not configured');

            return false;
        }

        $url = sprintf('%s/lists/%s/members', $this->getBaseUrl(), $this->listId);

        $data = [
            'email_address' => strtolower($email),
            'status_if_new' => 'subscribed',
        ];

        if (! empty($mergeFields)) {
            $data['merge_fields'] = $mergeFields;
        }

        try {
            $response = Http::withBasicAuth('anystring', $this->apiKey)
                ->put($url.'/'.md5(strtolower($email)), $data);

            if ($response->successful()) {
                Log::info('Mailchimp: Successfully subscribed '.$email);

                return true;
            }

            Log::warning('Mailchimp: Failed to subscribe '.$email, [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Mailchimp: Exception while subscribing '.$email, [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if an email is already subscribed
     */
    public function isSubscribed(string $email): bool
    {
        if (empty($this->apiKey) || empty($this->listId)) {
            return false;
        }

        $url = sprintf(
            '%s/lists/%s/members/%s',
            $this->getBaseUrl(),
            $this->listId,
            md5(strtolower($email))
        );

        try {
            $response = Http::withBasicAuth('anystring', $this->apiKey)->get($url);

            if ($response->successful()) {
                $data = $response->json();

                return isset($data['status']) && $data['status'] === 'subscribed';
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Mailchimp: Exception while checking subscription for '.$email, [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
