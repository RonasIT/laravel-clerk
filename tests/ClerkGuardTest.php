<?php

namespace RonasIT\Clerk\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use RonasIT\Clerk\Auth\ClerkGuard;
use RonasIT\Clerk\Exceptions\EmptyConfigException;

class ClerkGuardTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testAuthUser(): void
    {
        Config::set('clerk.allowed_issuer', 'some_issuer');
        Config::set('clerk.secret_key', 'some_secret_key');
        Config::set('clerk.signer_key_path', 'some_signer_key_path');

        $guard = app(ClerkGuard::class);

        $guard->setRequest(new Request());

        $this->assertEquals(true, $guard->guest());
    }

    public function testEmptyConfigException(): void
    {
        $this->expectException(EmptyConfigException::class);

        $this->expectExceptionMessage('One of required clerk config is empty.');

        app(ClerkGuard::class);
    }
}

