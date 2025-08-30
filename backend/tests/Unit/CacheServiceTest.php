<?php

namespace Tests\Unit;

use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    protected CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new CacheService();

        // Limpar cache antes de cada teste
        Cache::flush();
    }

    public function test_test_cache_creates_new_data_when_cache_is_empty()
    {
        // Arrange
        $cacheKey = 'unique_id_cache';

        // Act
        $result = $this->cacheService->testCache();

        // Assert
        $this->assertEquals('cache_miss', $result['status']);
        $this->assertEquals('Novos dados criados e salvos no cache por 60 segundos', $result['message']);
        $this->assertArrayHasKey('unique_id', $result['data']);
        $this->assertArrayHasKey('created_at', $result['data']);
        $this->assertArrayHasKey('created_timestamp', $result['data']);
        $this->assertArrayHasKey('expires_at', $result['data']);
        $this->assertEquals(60, $result['cache_remaining_seconds']);

        // Verificar se os dados foram salvos no cache
        $cachedData = Cache::get($cacheKey);
        $this->assertNotNull($cachedData);
        $this->assertEquals($result['data']['unique_id'], $cachedData['unique_id']);
    }

    public function test_test_cache_returns_existing_data_when_cache_has_data()
    {
        // Arrange
        $cacheKey = 'unique_id_cache';
        $existingData = [
            'unique_id' => 'test_id_123',
            'created_at' => now()->toISOString(),
            'created_timestamp' => time(),
            'expires_at' => now()->addSeconds(60)->toISOString(),
        ];
        Cache::put($cacheKey, $existingData, 60);

        // Act
        $result = $this->cacheService->testCache();

        // Assert
        $this->assertEquals('cache_hit', $result['status']);
        $this->assertEquals('Dados encontrados no cache', $result['message']);
        $this->assertEquals($existingData, $result['data']);
        $this->assertLessThanOrEqual(60, $result['cache_remaining_seconds']);
    }

    public function test_clear_test_cache_removes_cache_data()
    {
        // Arrange
        $cacheKey = 'unique_id_cache';
        $testData = ['test' => 'data'];
        Cache::put($cacheKey, $testData, 60);

        // Act
        $result = $this->cacheService->clearTestCache();

        // Assert
        $this->assertTrue($result);
        $this->assertNull(Cache::get($cacheKey));
    }

    public function test_clear_test_cache_returns_false_when_cache_is_already_empty()
    {
        // Act
        $result = $this->cacheService->clearTestCache();

        // Assert
        $this->assertFalse($result);
    }

    public function test_get_cache_stats_returns_correct_data_when_cache_exists()
    {
        // Arrange
        $cacheKey = 'unique_id_cache';
        $testData = [
            'unique_id' => 'test_id_123',
            'created_at' => now()->toISOString(),
            'created_timestamp' => time() - 30, // 30 segundos atrás
            'expires_at' => now()->addSeconds(30)->toISOString(),
        ];
        Cache::put($cacheKey, $testData, 60);

        // Act
        $stats = $this->cacheService->getCacheStats();

        // Assert
        $this->assertTrue($stats['cache_exists']);
        $this->assertEquals($testData, $stats['data']);
        $this->assertEquals(30, $stats['elapsed_seconds']);
        $this->assertEquals(30, $stats['remaining_seconds']);
        $this->assertEquals(50.0, $stats['progress_percentage']);
    }

    public function test_get_cache_stats_returns_correct_data_when_cache_does_not_exist()
    {
        // Act
        $stats = $this->cacheService->getCacheStats();

        // Assert
        $this->assertFalse($stats['cache_exists']);
        $this->assertEquals('Nenhum dado no cache', $stats['message']);
    }

    public function test_get_cache_stats_handles_edge_case_when_cache_is_expired()
    {
        // Arrange
        $cacheKey = 'unique_id_cache';
        $testData = [
            'unique_id' => 'test_id_123',
            'created_at' => now()->toISOString(),
            'created_timestamp' => time() - 70, // 70 segundos atrás (expired)
            'expires_at' => now()->subSeconds(10)->toISOString(),
        ];
        Cache::put($cacheKey, $testData, 60);

        // Act
        $stats = $this->cacheService->getCacheStats();

        // Assert
        $this->assertTrue($stats['cache_exists']);
        $this->assertEquals(0, $stats['remaining_seconds']);
        $this->assertEquals(60, $stats['elapsed_seconds']);
        $this->assertEquals(100.0, $stats['progress_percentage']);
    }
}
