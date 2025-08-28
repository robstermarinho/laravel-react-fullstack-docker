<?php

namespace Tests\Unit;

use App\Providers\AuthServiceProvider;
use App\Services\AuthService;
use Illuminate\Container\Container;
use Tests\TestCase;

class AuthServiceProviderTest extends TestCase
{
    protected AuthServiceProvider $provider;
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = app();
        $this->provider = new AuthServiceProvider($this->app);
    }

    public function test_auth_service_is_registered_as_singleton(): void
    {
        // Register the service
        $this->provider->register();

        // Verify that AuthService is bound in the container
        $this->assertTrue($this->app->bound(AuthService::class));

        // Verify it's registered as singleton
        $this->assertTrue($this->app->isShared(AuthService::class));
    }

    public function test_auth_service_returns_same_instance(): void
    {
        // Register the service
        $this->provider->register();

        // Get two instances
        $instance1 = $this->app->make(AuthService::class);
        $instance2 = $this->app->make(AuthService::class);

        // Verify they are the same instance (singleton behavior)
        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(AuthService::class, $instance1);
        $this->assertInstanceOf(AuthService::class, $instance2);
    }

    public function test_auth_service_can_be_resolved(): void
    {
        // Register the service
        $this->provider->register();

        // Resolve the service
        $authService = $this->app->make(AuthService::class);

        $this->assertInstanceOf(AuthService::class, $authService);
    }

    public function test_provider_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AuthServiceProvider::class, $this->provider);
    }

    public function test_boot_method_exists_and_can_be_called(): void
    {
        $this->assertTrue(method_exists($this->provider, 'boot'));

        // Call boot method - should not throw any exceptions
        $this->provider->boot();

        $this->assertTrue(true); // If we reach here, boot() executed successfully
    }

    public function test_register_method_exists_and_can_be_called(): void
    {
        $this->assertTrue(method_exists($this->provider, 'register'));

        // Call register method - should not throw any exceptions
        $this->provider->register();

        $this->assertTrue(true); // If we reach here, register() executed successfully
    }

    public function test_auth_service_singleton_with_dependency_injection(): void
    {
        // Register the service
        $this->provider->register();

        // Test that when the service is injected via constructor,
        // it returns the same singleton instance
        $resolvedFromContainer = $this->app->make(AuthService::class);

        // Create a class that depends on AuthService to test injection
        $testClass = new class($this->app->make(AuthService::class)) {
            public function __construct(
                public readonly AuthService $authService
            ) {}
        };

        $this->assertSame($resolvedFromContainer, $testClass->authService);
    }

    public function test_provider_integrates_with_laravel_service_container(): void
    {
        // Register the service
        $this->provider->register();

        // Test that the service can be resolved using different methods
        $method1 = $this->app->make(AuthService::class);
        $method2 = $this->app->get(AuthService::class);
        $method3 = resolve(AuthService::class);

        // All methods should return the same singleton instance
        $this->assertSame($method1, $method2);
        $this->assertSame($method2, $method3);
        $this->assertInstanceOf(AuthService::class, $method1);
    }
}
