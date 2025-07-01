<?php

namespace RonasIT\Clerk\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use RonasIT\Clerk\Auth\ClerkGuard;
use RonasIT\Clerk\Exceptions\EmptyConfigException;
use RonasIT\Clerk\Tests\Support\TokenMockTrait;

class ClerkGuardTest extends TestCase
{
    use TokenMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        Config::set('clerk', [
            'allowed_issuer' => 'issuer',
            'secret_key' => self::SECRET_KEY_PASS,
            'signer_key_path' => self::SIGNER_KEY_PATH,
        ]);
    }

    public function testAuthUser(): void
    {
        $clerkToken = $this
            ->createJWTToken('user_id')
            ->toString();

        $request = new Request();
        $request->headers->set('Authorization', "Bearer {$clerkToken}");

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertTrue($guard->check());
        $this->assertTrue($guard->validate([$clerkToken]));
        $this->assertEquals('user_id', $guard->id());
    }

    public function testAuthUserIssuerIsWrong(): void
    {
        $clerkToken = $this
            ->createJWTToken('user_id', 'wrong_issuer')
            ->toString();

        $request = new Request();
        $request->headers->set('Authorization', "Bearer {$clerkToken}");

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertFalse($guard->check());
    }

    public function testAuthUserInvalidToken(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', "Bearer NOT_JWT_TOKEN");

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertFalse($guard->check());
    }

    public function testGuest(): void
    {
        $guard = app(ClerkGuard::class)->setRequest(new Request());

        $this->assertTrue($guard->guest());
    }

    public function testEmptyConfigException(): void
    {
        Config::set('clerk', [
            'allowed_issuer' => null,
            'secret_key' => null,
            'signer_key_path' => null,
        ]);

        $this->expectException(EmptyConfigException::class);

        $this->expectExceptionMessage('One of required clerk config is empty.');

        app(ClerkGuard::class);
    }
}
