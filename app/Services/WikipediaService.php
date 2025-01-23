<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WikipediaService
{
    private const CACHE_TTL = 86400; // 24 hour cache
    private const RATE_LIMIT_DELAY = 250000; // 0.25 seconds between requests

    // Map country codes to Wikipedia article titles
    private const COUNTRY_MAP = [
        'GB' => 'Great_Britain',
        'NL' => 'Netherlands',
        'DE' => 'Germany',
        'FR' => 'France',
        'ES' => 'Spain',
        'IT' => 'Italy',
        'GR' => 'Greece'
    ];

    public function getCountryIntroduction(string $countryCode): ?array
    {
        $countryCode = strtoupper($countryCode);
        
        if (!isset(self::COUNTRY_MAP[$countryCode])) {
            return null;
        }

        return Cache::remember(
            "wiki_intro_{$countryCode}",
            self::CACHE_TTL,
            function () use ($countryCode) {
                usleep(self::RATE_LIMIT_DELAY);

                try {
                    $response = Http::get(
                        "https://en.wikipedia.org/api/rest_v1/page/summary/" . 
                        self::COUNTRY_MAP[$countryCode]
                    );

                    if (!$response->successful()) {
                        Log::error('Wikipedia API error', [
                            'country' => $countryCode,
                            'status' => $response->status(),
                            'response' => $response->json()
                        ]);
                        return null;
                    }

                    $data = $response->json();
                    
                    return [
                        'title' => $data['title'] ?? null,
                        'extract' => $data['extract'] ?? null
                    ];
                } catch (\Exception $e) {
                    Log::error('Wikipedia API exception', [
                        'country' => $countryCode,
                        'message' => $e->getMessage()
                    ]);
                    return null;
                }
            }
        );
    }

    public function getMultipleCountries(?array $countries = null): array
    {
        $countries = $countries ?? array_keys(self::COUNTRY_MAP);
        $results = [];

        foreach ($countries as $country) {
            $data = $this->getCountryIntroduction($country);
            if ($data) {
                $results[$country] = $data;
            }
        }

        return $results;
    }
}