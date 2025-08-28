<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can logout successfully
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // First verify token works before logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');
        $response->assertStatus(200);

        // Perform logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        // Verify token is revoked by trying to use it again
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid token'
            ]);
    }

    /**
     * Test unauthenticated user cannot logout
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test logout with invalid bearer token
     */
    public function test_logout_with_invalid_bearer_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-123',
        ])->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test logout with malformed bearer token
     */
    public function test_logout_with_malformed_bearer_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearertoken123', // Missing space
        ])->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test logout with empty bearer token
     */
    public function test_logout_with_empty_bearer_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ',
        ])->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test logout without authorization header
     */
    public function test_logout_without_authorization_header(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test logout with wrong authorization type
     */
    public function test_logout_with_wrong_authorization_type(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . $token, // Should be Bearer
        ])->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test logout with already revoked token
     */
    public function test_logout_with_already_revoked_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        $plainTextToken = $token->plainTextToken;

        // Revoke the token first
        $token->accessToken->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $plainTextToken,
        ])->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test logout with wrong HTTP method
     */
    public function test_logout_with_wrong_http_method(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Test GET method
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/logout');
        $response->assertStatus(405); // Method Not Allowed

        // Test PUT method
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/logout');
        $response->assertStatus(405); // Method Not Allowed

        // Test DELETE method
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/logout');
        $response->assertStatus(405); // Method Not Allowed
    }

    /**
     * Test logout only revokes current token, not all user tokens
     */
    public function test_logout_only_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('token-1')->plainTextToken;
        $token2 = $user->createToken('token-2')->plainTextToken;

        // Logout with token1
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        // token1 should be revoked
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->getJson('/api/user');
        $response->assertStatus(401);

        // token2 should not be revoked
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
        ])->getJson('/api/user');
        $response->assertStatus(200);
    }

    /**
     * Test multiple logout attempts with same token
     */
    public function test_multiple_logout_attempts_with_same_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // First logout should succeed
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        // Second logout with same token should fail
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response2->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid token'
            ]);
    }




    /**
     * Test logout response structure
     */
    public function test_logout_response_structure(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ])
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        // Verify response contains only message, no sensitive data
        $responseData = $response->json();
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('message', $responseData);
    }
}
