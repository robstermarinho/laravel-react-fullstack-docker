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

        // Tenta pegar do cache
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            // Se existe no cache, retorna os dados existentes
            Log::info('Cache hit - retornando dados existentes', $cachedData);

            return [
                'status' => 'cache_hit',
                'message' => 'Dados encontrados no cache',
                'data' => $cachedData,
                'cache_remaining_seconds' => $this->getCacheRemainingTime($cacheKey),
            ];
        }

        // Se não existe no cache, cria novos dados
        $newData = [
            'unique_id' => uniqid('cache_', true),
            'created_at' => now()->toISOString(),
            'created_timestamp' => time(),
            'expires_at' => now()->addSeconds(60)->toISOString(),
        ];

        // Salva no cache por 60 segundos
        Cache::put($cacheKey, $newData, 60);

        Log::info('Cache miss - criando novos dados', $newData);

        return [
            'status' => 'cache_miss',
            'message' => 'Novos dados criados e salvos no cache por 60 segundos',
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
        // Fallback: calcular baseado no timestamp salvo
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

        Log::info('Cache de teste limpo', ['success' => $result]);

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
                'message' => 'Nenhum dado no cache',
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
