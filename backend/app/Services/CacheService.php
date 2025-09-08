<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Test cache functionality with unique ID that renews every 60 seconds
     *
     * @return array
     */
    public function testCache(): array
    {

        $cacheKey = 'unique_id_cache';

        // Try to get from cache
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            // If exists in cache, return existing data
            Log::info('Cache hit - returning existing data', $cachedData);

            return [
                'status' => 'cache_hit',
                'message' => 'Data found in cache',
                'data' => $cachedData,
                'cache_remaining_seconds' => $this->getCacheRemainingTime($cacheKey),
            ];
        }

        // If not in cache, create new data
        $newData = [
            'unique_id' => uniqid('cache_', true),
            'created_at' => now()->toISOString(),
            'created_timestamp' => time(),
            'expires_at' => now()->addSeconds(60)->toISOString(),
        ];

        // Save to cache for 60 seconds
        Cache::put($cacheKey, $newData, 60);

        Log::info('Cache miss - creating new data', $newData);

        return [
            'status' => 'cache_miss',
            'message' => 'New data created and saved to cache for 60 seconds',
            'data' => $newData,
            'cache_remaining_seconds' => 60,
        ];
    }

    /**
     * Get remaining time for cache key
     *
     * @param string $cacheKey
     * @return int
     */
    private function getCacheRemainingTime(string $cacheKey): int
    {
        // Fallback: calculate based on saved timestamp
        $cachedData = Cache::get($cacheKey);
        if ($cachedData && isset($cachedData['created_timestamp'])) {
            $elapsed = time() - $cachedData['created_timestamp'];
            $remaining = 60 - $elapsed;
            return max(0, $remaining);
        }

        return 0;
    }

    /**
     * Clear the test cache
     *
     * @return bool
     */
    public function clearTestCache(): bool
    {
        $result = Cache::forget('unique_id_cache');

        Log::info('Test cache cleared', ['success' => $result]);

        return $result;
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getCacheStats(): array
    {
        $cacheKey = 'unique_id_cache';
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return [
                'cache_exists' => false,
                'message' => 'No data in cache',
            ];
        }

        $remaining = $this->getCacheRemainingTime($cacheKey);
        $elapsed = 60 - $remaining;

        return [
            'cache_exists' => true,
            'data' => $cachedData,
            'elapsed_seconds' => $elapsed,
            'remaining_seconds' => $remaining,
            'progress_percentage' => round(($elapsed / 60) * 100, 2),
        ];
    }
}
