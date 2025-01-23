<?php

namespace App\Http\Controllers;

use App\Services\YouTubeService;
use App\Services\WikipediaService;
use Illuminate\Http\Request;

class CountryVideoController extends Controller
{
    protected $youtubeService;
    protected $wikipediaService;

    public function __construct(YouTubeService $youtubeService, WikipediaService $wikipediaService)
    {
        $this->youtubeService = $youtubeService;
        $this->wikipediaService = $wikipediaService;
    }

    public function index(Request $request)
    {
        $countries = $request->input('countries', ['GB', 'NL', 'DE', 'FR', 'ES', 'IT', 'GR']);
        $results = [];

        foreach ($countries as $country) {
            $youtubeData = $this->youtubeService->getMostPopularByCountry($country);
            $wikiData = $this->wikipediaService->getCountryIntroduction($country);

            if ($youtubeData && $wikiData) {
                $results[$country] = [
                    'youtube' => [
                        'description' => $youtubeData['description'],
                        'thumbnails' => [
                            'sd' => $youtubeData['thumbnails']['sd'] ?? null,
                            'hd' => $youtubeData['thumbnails']['hd'] ?? null,
                        ],
                    ],
                    'wikipedia' => [
                        'title' => $wikiData['title'],
                        'extract' => $wikiData['extract'],
                    ],
                ];
            }
        }

        return response()->json($results);
    }
}
