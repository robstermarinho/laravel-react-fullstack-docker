<?php

namespace Tests\Unit;

use App\Contracts\AuthServiceInterface;
use App\Services\AuthService;
use Tests\TestCase;
use App\Models\User;

class AuthServiceProviderTest extends TestCase
{

    public function test_it_binds_interface_to_concrete()
    {
        $resolved = app(AuthServiceInterface::class);

        $this->assertInstanceOf(
            AuthService::class,
            $resolved,
            'AuthServiceInterface should resolve to AuthService'
        );
    }


    public function test_it_registers_as_singleton()
    {
        $first  = app(AuthServiceInterface::class);
        $second = app(AuthServiceInterface::class);

        $this->assertSame(
            $first,
            $second,
            'AuthServiceInterface should be a singleton (same instance in multiple resolutions)'
        );
    }
}
