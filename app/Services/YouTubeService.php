<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    // Default countries as specified in requirements
    private const COUNTRIES = ['GB', 'NL', 'DE', 'FR', 'ES', 'IT', 'GR'];
    private const CACHE_TTL = 1800; // Half hour cache
    private const RATE_LIMIT_DELAY = 1000000; // 1 seconds between requests

    public function getMostPopularByCountry(string $countryCode): ?array
    {
        $countryCode = strtoupper($countryCode);
        
        // Validate country code
        if (!in_array($countryCode, self::COUNTRIES)) {
            return null;
        }

        // Check cache first
        return Cache::remember(
            "youtube_popular_{$countryCode}", 
            self::CACHE_TTL,
            function () use ($countryCode) {
                // Basic rate limiting
                usleep(self::RATE_LIMIT_DELAY);

                try {
                    $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
                        'part' => 'snippet',
                        'chart' => 'mostPopular',
                        'regionCode' => $countryCode,
                        'maxResults' => 1,
                        'key' => config('services.google.youtube_api_key')
                    ]);

                    if (!$response->successful()) {
                        Log::error('YouTube API error', [
                            'country' => $countryCode,
                            'status' => $response->status(),
                            'response' => $response->json()
                        ]);
                        return null;
                    }

                    $video = $response->json()['items'][0] ?? null;
                    if (!$video) return null;

                    // Return only required fields
                    return [
                        'description' => $video['snippet']['description'],
                        'thumbnails' => [
                            'normal' => $video['snippet']['thumbnails']['medium'] ?? null,
                            'high' => $video['snippet']['thumbnails']['high'] ?? null
                        ]
                    ];
                } catch (\Exception $e) {
                    Log::error('YouTube API exception', [
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
        $countries = $countries ?? self::COUNTRIES;
        $results = [];

        foreach ($countries as $country) {
            $data = $this->getMostPopularByCountry($country);
            if ($data) {
                $results[$country] = $data;
            }
        }

        return $results;
    }
}