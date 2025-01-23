<?php

namespace App\Http\Controllers;

use App\Services\YouTubeService;
use App\Services\WikipediaService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Country Videos API",
 *     version="1.0.0",
 *     description="API to fetch YouTube videos and Wikipedia data for countries"
 * )
 */
class CountryVideoController extends Controller
{
    protected $youtubeService;
    protected $wikipediaService;

    public function __construct(YouTubeService $youtubeService, WikipediaService $wikipediaService)
    {
        $this->youtubeService = $youtubeService;
        $this->wikipediaService = $wikipediaService;
    }

    /**
     * @OA\Get(
     *     path="/api/country-videos",
     *     summary="Get country videos and info",
     *     tags={"Countries"},
     *     @OA\Parameter(
     *         name="countries[]",
     *         in="query",
     *         description="List of country codes to fetch data for",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="string", enum={"GB","NL","DE","FR","ES","IT","GR"})
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Number of records to skip",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=0, default=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="country_code", type="string", example="GB"),
     *                     @OA\Property(property="youtube", type="object",
     *                         @OA\Property(property="description", type="string"),
     *                         @OA\Property(property="thumbnails", type="object",
     *                             @OA\Property(property="sd", type="string", format="uri"),
     *                             @OA\Property(property="hd", type="string", format="uri")
     *                         )
     *                     ),
     *                     @OA\Property(property="wikipedia", type="object",
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="extract", type="string")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer", example=7),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total_pages", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $countries = $request->input('countries', ['GB', 'NL', 'DE', 'FR', 'ES', 'IT', 'GR']);
        $page = (int)$request->input('page', 1);
        $offset = (int)$request->input('offset', 0);
        $perPage = 10;

        $results = [];
        foreach ($countries as $country) {
            $youtubeData = $this->youtubeService->getMostPopularByCountry($country);
            $wikiData = $this->wikipediaService->getCountryIntroduction($country);

            if ($youtubeData && $wikiData) {
                $results[] = [
                    'country_code' => $country,
                    'youtube' => $youtubeData,
                    'wikipedia' => $wikiData
                ];
            }
        }

        $total = count($results);
        $items = array_slice($results, $offset, $perPage);

        return response()->json([
            'data' => $items,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }
}
