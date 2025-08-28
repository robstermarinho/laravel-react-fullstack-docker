<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_register_creates_user_successfully(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertEquals('Bearer', $result['token_type']);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertTrue(Hash::check('password123', $result['user']->password));
    }

    public function test_register_throws_validation_exception_for_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ];

        $this->authService->register($invalidData);
    }

    public function test_register_throws_validation_exception_for_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $this->expectException(ValidationException::class);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $this->authService->register($userData);
    }

    public function test_validate_register_returns_validator(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = $this->authService->validateRegister($data);

        $this->assertInstanceOf(\Illuminate\Contracts\Validation\Validator::class, $validator);
        $this->assertFalse($validator->fails());
    }

    public function test_validate_register_fails_for_invalid_data(): void
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ];

        $validator = $this->authService->validateRegister($data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validate_login_returns_validator(): void
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $validator = $this->authService->validateLogin($data);

        $this->assertInstanceOf(\Illuminate\Contracts\Validation\Validator::class, $validator);
        $this->assertFalse($validator->fails());
    }

    public function test_validate_login_fails_for_invalid_data(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => '123'
        ];

        $validator = $this->authService->validateLogin($data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_login_authenticates_user_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertEquals($user->id, $result['user']->id);
    }

    public function test_login_throws_exception_for_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $this->authService->login($credentials);
    }

    public function test_login_throws_validation_exception_for_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $invalidCredentials = [
            'email' => 'invalid-email',
            'password' => '123'
        ];

        $this->authService->login($invalidCredentials);
    }

    public function test_logout_revokes_token_successfully(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        $plainTextToken = $token->plainTextToken;

        $result = $this->authService->logout($plainTextToken);

        $this->assertTrue($result);
        $this->assertNull(PersonalAccessToken::findToken($plainTextToken));
    }

    public function test_logout_throws_exception_for_empty_token(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token not found');

        $this->authService->logout('');
    }

    public function test_logout_throws_exception_for_invalid_token(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid token');

        $this->authService->logout('invalid-token');
    }

    public function test_get_user_returns_user_successfully(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        $plainTextToken = $token->plainTextToken;

        $result = $this->authService->getUser($plainTextToken);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_get_user_throws_exception_for_empty_token(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token not found');

        $this->authService->getUser('');
    }

    public function test_get_user_throws_exception_for_invalid_token(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid token');

        $this->authService->getUser('invalid-token');
    }

    public function test_is_valid_token_returns_true_for_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        $plainTextToken = $token->plainTextToken;

        $result = $this->authService->isValidToken($plainTextToken);

        $this->assertTrue($result);
    }

    public function test_is_valid_token_returns_false_for_invalid_token(): void
    {
        $result = $this->authService->isValidToken('invalid-token');

        $this->assertFalse($result);
    }

    public function test_is_valid_token_returns_false_for_empty_token(): void
    {
        $result = $this->authService->isValidToken('');

        $this->assertFalse($result);
    }
}
