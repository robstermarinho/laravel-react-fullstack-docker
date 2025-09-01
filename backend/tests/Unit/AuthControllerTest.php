<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\AuthController;
use App\Contracts\AuthServiceInterface;
use App\Services\CacheService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends TestCase
{
    protected AuthServiceInterface $mockAuthService;
    protected CacheService $mockCacheService;
    protected AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuthService = Mockery::mock(AuthServiceInterface::class);
        $this->mockCacheService = Mockery::mock(CacheService::class);
        $this->controller = new AuthController($this->mockAuthService, $this->mockCacheService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_register_returns_successful_response(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $expectedResult = [
            'user' => new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'])->toArray(),
            'token' => 'test-token',
            'token_type' => 'Bearer'
        ];

        $request = Request::create('/', 'POST', $requestData);

        $this->mockAuthService
            ->expects('register')
            ->with($requestData)
            ->andReturn($expectedResult);

        $response = $this->controller->register($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals($expectedResult, $response->getData(true));
    }

    public function test_register_handles_validation_exception(): void
    {
        $requestData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ];

        $request = Request::create('/', 'POST', $requestData);

        $validationException = new ValidationException(
            validator($requestData, ['email' => 'required|email'])
        );

        $this->mockAuthService
            ->expects('register')
            ->with($requestData)
            ->andThrow($validationException);

        $response = $this->controller->register($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function test_register_handles_general_exception(): void
    {
        $requestData = ['name' => 'Test'];
        $request = Request::create('/', 'POST', $requestData);

        $this->mockAuthService
            ->expects('register')
            ->with($requestData)
            ->andThrow(new \Exception('Database connection failed'));

        $response = $this->controller->register($request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Registration failed', $responseData['message']);
        $this->assertEquals('Database connection failed', $responseData['error']);
    }

    public function test_login_returns_successful_response(): void
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);

        $expectedResult = [
            'user' => $user,
            'token' => 'test-token',
            'token_type' => 'Bearer'
        ];

        // What we expect in the JSON response (user object will be serialized to array)
        $expectedJsonResponse = [
            'user' => $user->toArray(),
            'token' => 'test-token',
            'token_type' => 'Bearer'
        ];

        $request = Request::create('/', 'POST', $credentials);

        $this->mockAuthService
            ->expects('login')
            ->with($credentials)
            ->andReturn($expectedResult);

        $response = $this->controller->login($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($expectedJsonResponse, $response->getData(true));
    }

    public function test_login_handles_validation_exception(): void
    {
        $credentials = [
            'email' => 'invalid-email',
            'password' => '123'
        ];

        $request = Request::create('/', 'POST', $credentials);

        $validationException = new ValidationException(
            validator($credentials, ['email' => 'required|email'])
        );

        $this->mockAuthService
            ->expects('login')
            ->with($credentials)
            ->andThrow($validationException);

        $response = $this->controller->login($request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function test_login_handles_authentication_failure(): void
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $request = Request::create('/', 'POST', $credentials);

        $this->mockAuthService
            ->expects('login')
            ->with($credentials)
            ->andThrow(new \Exception('Invalid credentials'));

        $response = $this->controller->login($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Invalid credentials', $responseData['message']);
    }

    public function test_logout_returns_successful_response(): void
    {
        $token = 'test-token';
        $request = Request::create('/', 'POST');
        $request->headers->set('Authorization', "Bearer {$token}");

        $this->mockAuthService
            ->expects('logout')
            ->with($token)
            ->andReturn(true);

        $response = $this->controller->logout($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Successfully logged out', $responseData['message']);
    }

    public function test_logout_handles_missing_token(): void
    {
        $request = Request::create('/', 'POST');

        $response = $this->controller->logout($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Token not provided', $responseData['message']);
    }

    public function test_logout_handles_service_exception(): void
    {
        $token = 'invalid-token';
        $request = Request::create('/', 'POST');
        $request->headers->set('Authorization', "Bearer {$token}");

        $this->mockAuthService
            ->expects('logout')
            ->with($token)
            ->andThrow(new \Exception('Invalid token'));

        $response = $this->controller->logout($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Invalid token', $responseData['message']);
    }

    public function test_user_returns_successful_response(): void
    {
        $token = 'test-token';
        $user = new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);

        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', "Bearer {$token}");

        $this->mockAuthService
            ->expects('getUser')
            ->with($token)
            ->andReturn($user);

        $response = $this->controller->user($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals($user->toArray(), $responseData['user']);
    }

    public function test_user_handles_missing_token(): void
    {
        $request = Request::create('/', 'GET');

        $response = $this->controller->user($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Token not provided', $responseData['message']);
    }

    public function test_user_handles_invalid_token(): void
    {
        $token = 'invalid-token';
        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', "Bearer {$token}");

        $this->mockAuthService
            ->expects('getUser')
            ->with($token)
            ->andThrow(new \Exception('Invalid token'));

        $response = $this->controller->user($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Invalid token', $responseData['message']);
    }

    public function test_test_cache_returns_successful_response(): void
    {
        $request = Request::create('/', 'POST');
        $expectedResult = ['test_id' => 'unique-id', 'data' => 'cached-data'];

        $this->mockCacheService
            ->expects('testCache')
            ->andReturn($expectedResult);

        $response = $this->controller->testCache($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Cache test executed', $responseData['message']);
        $this->assertEquals($expectedResult, $responseData['result']);
        $this->assertArrayHasKey('timestamp', $responseData);
    }

    public function test_test_cache_handles_exception(): void
    {
        $request = Request::create('/', 'POST');

        $this->mockCacheService
            ->expects('testCache')
            ->andThrow(new \Exception('Cache service unavailable'));

        $response = $this->controller->testCache($request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Cache test failed', $responseData['message']);
        $this->assertEquals('Cache service unavailable', $responseData['error']);
    }

    public function test_get_cache_stats_returns_successful_response(): void
    {
        $request = Request::create('/', 'GET');
        $expectedStats = ['hits' => 10, 'misses' => 2, 'total_keys' => 5];

        $this->mockCacheService
            ->expects('getCacheStats')
            ->andReturn($expectedStats);

        $response = $this->controller->getCacheStats($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Cache statistics retrieved', $responseData['message']);
        $this->assertEquals($expectedStats, $responseData['stats']);
        $this->assertArrayHasKey('timestamp', $responseData);
    }

    public function test_get_cache_stats_handles_exception(): void
    {
        $request = Request::create('/', 'GET');

        $this->mockCacheService
            ->expects('getCacheStats')
            ->andThrow(new \Exception('Failed to retrieve stats'));

        $response = $this->controller->getCacheStats($request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Failed to get cache statistics', $responseData['message']);
        $this->assertEquals('Failed to retrieve stats', $responseData['error']);
    }

    public function test_clear_test_cache_returns_successful_response(): void
    {
        $request = Request::create('/', 'DELETE');

        $this->mockCacheService
            ->expects('clearTestCache')
            ->andReturn(true);

        $response = $this->controller->clearTestCache($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Cache cleared successfully', $responseData['message']);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('timestamp', $responseData);
    }

    public function test_clear_test_cache_handles_already_empty_cache(): void
    {
        $request = Request::create('/', 'DELETE');

        $this->mockCacheService
            ->expects('clearTestCache')
            ->andReturn(false);

        $response = $this->controller->clearTestCache($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Cache was already empty', $responseData['message']);
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('timestamp', $responseData);
    }

    public function test_clear_test_cache_handles_exception(): void
    {
        $request = Request::create('/', 'DELETE');

        $this->mockCacheService
            ->expects('clearTestCache')
            ->andThrow(new \Exception('Cache clearing failed'));

        $response = $this->controller->clearTestCache($request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Failed to clear cache', $responseData['message']);
        $this->assertEquals('Cache clearing failed', $responseData['error']);
    }

    public function test_extract_bearer_token_returns_token(): void
    {
        $token = 'test-token-123';
        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', "Bearer {$token}");

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('extractBearerToken');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $request);

        $this->assertEquals($token, $result);
    }

    public function test_extract_bearer_token_throws_exception_for_missing_token(): void
    {
        $request = Request::create('/', 'GET');

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('extractBearerToken');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token not provided');

        $method->invoke($this->controller, $request);
    }

    public function test_validation_error_response_returns_proper_format(): void
    {
        $validationException = new ValidationException(
            validator(['email' => 'invalid'], ['email' => 'required|email'])
        );

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validationErrorResponse');
        $method->setAccessible(true);

        $response = $method->invoke($this->controller, $validationException);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }
}
