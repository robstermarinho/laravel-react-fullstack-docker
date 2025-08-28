<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user login
     */
    public function test_user_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ],
                'token',
                'token_type'
            ])
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => 'john@example.com'
                ],
                'token_type' => 'Bearer'
            ]);
    }

    /**
     * Test login with wrong password
     */
    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }

    /**
     * Test login with non-existent email
     */
    public function test_login_fails_with_non_existent_email(): void
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }

    /**
     * Test login with missing email
     */
    public function test_login_fails_with_missing_email(): void
    {
        $loginData = [
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login with missing password
     */
    public function test_login_fails_with_missing_password(): void
    {
        $loginData = [
            'email' => 'john@example.com'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test login with invalid email format
     */
    public function test_login_fails_with_invalid_email_format(): void
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login with short password
     */
    public function test_login_fails_with_short_password(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => '123' // Less than 8 characters
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test login with empty email
     */
    public function test_login_fails_with_empty_email(): void
    {
        $loginData = [
            'email' => '',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login with empty password
     */
    public function test_login_fails_with_empty_password(): void
    {
        $loginData = [
            'email' => 'john@example.com',
            'password' => ''
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test login with all fields missing
     */
    public function test_login_fails_with_all_fields_missing(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test login with wrong HTTP method
     */
    public function test_login_fails_with_wrong_http_method(): void
    {
        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->getJson('/api/login');
        $response->assertStatus(405); // Method Not Allowed

        $response = $this->putJson('/api/login', $loginData);
        $response->assertStatus(405); // Method Not Allowed

        $response = $this->deleteJson('/api/login');
        $response->assertStatus(405); // Method Not Allowed
    }

    /**
     * Test login with bearer token (should not affect login)
     */
    public function test_login_with_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        // Create another user to get a token
        $anotherUser = User::factory()->create();
        $token = $anotherUser->createToken('test-token')->plainTextToken;

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => 'john@example.com'
                ],
                'token_type' => 'Bearer'
            ]);
    }

    /**
     * Test login with invalid bearer token (should not affect login)
     */
    public function test_login_with_invalid_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-123',
        ])->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => 'john@example.com'
                ],
                'token_type' => 'Bearer'
            ]);
    }

    /**
     * Test multiple successful logins create different tokens
     */
    public function test_multiple_logins_create_different_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response1 = $this->postJson('/api/login', $loginData);
        $response2 = $this->postJson('/api/login', $loginData);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $token1 = $response1->json('token');
        $token2 = $response2->json('token');

        $this->assertNotEquals($token1, $token2);
    }

    /**
     * Test login with special characters in password
     */
    public function test_login_with_special_characters_in_password(): void
    {
        $password = 'P@ssw0rd!#$%^&*()';
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make($password)
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => $password
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => 'john@example.com'
                ],
                'token_type' => 'Bearer'
            ]);
    }
}
