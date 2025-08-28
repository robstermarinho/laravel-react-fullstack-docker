<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test health endpoint returns success
     */
    public function test_health_endpoint_returns_success(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ok',
                'time'
            ])
            ->assertJson([
                'ok' => true
            ]);
    }

    /**
     * Test health endpoint with wrong HTTP method
     */
    public function test_health_endpoint_with_wrong_http_method(): void
    {
        $response = $this->postJson('/api/health');
        $response->assertStatus(405); // Method Not Allowed

        $response = $this->putJson('/api/health');
        $response->assertStatus(405); // Method Not Allowed

        $response = $this->deleteJson('/api/health');
        $response->assertStatus(405); // Method Not Allowed
    }

    /**
     * Test health endpoint accepts GET requests without authentication
     */
    public function test_health_endpoint_without_authentication(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
    }

    /**
     * Test health endpoint with bearer token (should still work)
     */
    public function test_health_endpoint_with_bearer_token(): void
    {
        // Create a user to get a token
        $user = \App\Models\User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'ok' => true
            ]);
    }

    /**
     * Test health endpoint with invalid bearer token (should still work)
     */
    public function test_health_endpoint_with_invalid_bearer_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-123',
        ])->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'ok' => true
            ]);
    }
}
