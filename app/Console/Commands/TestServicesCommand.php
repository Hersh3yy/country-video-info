<?php

namespace App\Console\Commands;

use App\Services\YouTubeService;
use App\Services\WikipediaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TestServicesCommand extends Command
{
    protected $signature = 'app:test-services {country?}';
    protected $description = 'Test YouTube and Wikipedia services';

    /**
     * Execute the console command.
     */
    public function handle(YouTubeService $youtube, WikipediaService $wiki)
    {
        $country = $this->argument('country');

        if ($country) {
            $this->info("Testing single country: $country");
            $this->testSingleCountry($youtube, $wiki, $country);
        } else {
            $this->info("Testing all countries");
            $this->testAllCountries($youtube, $wiki);
        }

        $this->info("\nCache keys:");
        $this->displayCacheKeys();

        // Test Redis connection
        Cache::put('test_key', 'test_value', 60); // Store a value for 60 seconds
        $value = Cache::get('test_key'); // Retrieve the value
    }

    private function testAllCountries($youtube, $wiki): void
    {
        $youtubeData = $youtube->getMultipleCountries();
        $wikiData = $wiki->getMultipleCountries();

        foreach (array_keys($youtubeData) as $country) {
            $this->info("\n=== $country ===");
            $this->displayCountryData($youtubeData[$country], $wikiData[$country]);
        }
    }

    private function testSingleCountry($youtube, $wiki, $country): void
    {
        $youtubeData = $youtube->getMostPopularByCountry($country);
        $wikiData = $wiki->getCountryIntroduction($country);
        $this->displayCountryData($youtubeData, $wikiData);
    }

    private function displayCountryData($youtubeData, $wikiData): void
    {
        $this->info("\nYouTube Data:");
        $this->table(['Field', 'Value'], [
            ['Description', substr($youtubeData['description'] ?? 'N/A', 0, 50) . '...'],
            ['Thumbnail URL', $youtubeData['thumbnails']['normal']['url'] ?? 'N/A']
        ]);

        $this->info("\nWikipedia Data:");
        $this->table(['Field', 'Value'], [
            ['Title', $wikiData['title'] ?? 'N/A'],
            ['Extract', substr($wikiData['extract'] ?? 'N/A', 0, 50) . '...']
        ]);
    }

    private function displayCacheKeys(): void
    {
        // For Redis
        if (config('cache.default') === 'redis') {
            $redis = Cache::getRedis();
            $keys = $redis->keys('*');
            foreach ($keys as $key) {
                $ttl = $redis->ttl($key);
                $this->line("$key (TTL: {$ttl}s)");
            }
        }
        // For file cache
        else {
            $files = glob(storage_path('framework/cache/data/*'));
            foreach ($files as $file) {
                $key = basename($file);
                $this->line($key);
            }
        }
    }
}
