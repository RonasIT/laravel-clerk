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

    public function testAuthUser(): void
    {
        Config::set('clerk', [
            'allowed_issuer' => 'some_issuer',
            'secret_key' => self::SECRET_KEY_PASS,
            'signer_key_path' => self::SIGNER_KEY_PATH,
        ]);

        $clerkToken = $this
            ->createJWTToken('some_user_id')
            ->toString();

        $guard = app(ClerkGuard::class);

        $request = new Request();
        $request->headers->set('Authorization', "Bearer {$clerkToken}");

        $guard->setRequest($request);

        $this->assertEquals(true, $guard->check());
        $this->assertEquals('some_user_id', $guard->id());
        $this->assertEquals(true, $guard->validate([$clerkToken]));
    }

    public function testAuthUserInvalidToken(): void
    {
        Config::set('clerk', [
            'allowed_issuer' => 'some_issuer',
            'secret_key' => 'some_secret_key',
            'signer_key_path' => 'some_signer_key_path',
        ]);

        $guard = app(ClerkGuard::class);

        $request = new Request();
        $request->headers->set('Authorization', "Bearer NOT_JWT_TOKEN");

        $guard->setRequest($request);

        $this->assertEquals(false, $guard->check());
    }

    public function testAuthUserIssuerIsWrong(): void
    {
        Config::set('clerk', [
            'allowed_issuer' => 'some_wrong_issuer',
            'secret_key' => self::SECRET_KEY_PASS,
            'signer_key_path' => self::SIGNER_KEY_PATH,
        ]);

        $clerkToken = $this
            ->createJWTToken('some_user_id')
            ->toString();

        $guard = app(ClerkGuard::class);

        $request = new Request();
        $request->headers->set('Authorization', "Bearer {$clerkToken}");

        $guard->setRequest($request);

        $this->assertEquals(false, $guard->check());
    }

    public function testGuest(): void
    {
        Config::set('clerk', [
            'allowed_issuer' => 'some_issuer',
            'secret_key' => 'some_secret_key',
            'signer_key_path' => 'some_signer_key_path',
        ]);

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
