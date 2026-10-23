<?php

namespace RonasIT\Clerk\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Clerk\Auth\ClerkGuard;
use RonasIT\Clerk\Exceptions\EmptyConfigException;
use RonasIT\Clerk\Exceptions\InvalidConfigException;
use RonasIT\Clerk\Tests\Support\ClerkGuardTestTrait;
use RonasIT\Clerk\Traits\TokenMockTrait;

class ClerkGuardTest extends TestCase
{
    use ClerkGuardTestTrait;
    use TokenMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        list($signerCert) = $this->generateCertificates();

        Config::set('clerk', [
            'allowed_issuer' => 'issuer',
            'allowed_origins' => ['https://app.example.com'],
            'secret_key' => self::SECRET_KEY_PASS,
            'signer_key' => base64_encode($signerCert),
        ]);
    }

    public function testAuthUser(): void
    {
        $clerkToken = $this
            ->createJWTToken('user_id')
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertTrue($guard->check());
        $this->assertTrue($guard->validate([$clerkToken]));
        $this->assertEquals('user_id', $guard->id());
    }

    public function testAuthUserWithSignerKeyPath(): void
    {
        $clerkToken = $this
            ->createJWTToken('user_id')
            ->toString();

        $signerKeyPath = $this->persistSignerKeyToFile();

        Config::set('clerk.signer_key', null);
        Config::set('clerk.signer_key_path', $signerKeyPath);

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertTrue($guard->check());
        $this->assertTrue($guard->validate([$clerkToken]));
        $this->assertEquals('user_id', $guard->id());
    }

    public function testAuthUserWithCustomClaims(): void
    {
        $customClaims = $this->getJsonFixture('user_custom_claims');

        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                claims: $customClaims,
            )
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $claims = $guard
            ->decodeToken($guard->getToken())
            ->claims()
            ->all();

        $this->assertEquals(
            expected: $customClaims,
            actual: array_intersect_key($claims, $customClaims),
        );
    }

    public function testMalformedSignerKeyConfigException(): void
    {
        Config::set('clerk.signer_key', 'not a valid base64 key');

        $this->expectException(InvalidConfigException::class);

        $this->expectExceptionMessage('The "clerk.signer_key" config must contain a base64-encoded PEM public key.');

        app(ClerkGuard::class);
    }

    public function testRawPemSignerKeyConfigException(): void
    {
        list($signerCert) = $this->generateCertificates();

        Config::set('clerk.signer_key', $signerCert);

        $this->expectException(InvalidConfigException::class);

        app(ClerkGuard::class);
    }

    public function testMalformedSignerKeyPathConfigException(): void
    {
        Config::set('clerk.signer_key', null);
        Config::set('clerk.signer_key_path', 'storage/framework/testing/missing_clerk_key.pem');

        $this->expectException(InvalidConfigException::class);

        $this->expectExceptionMessage('The "clerk.signer_key_path" config must point to a readable PEM public key file.');

        app(ClerkGuard::class);
    }

    public function testAuthUserIssuerIsWrong(): void
    {
        $clerkToken = $this
            ->createJWTToken('user_id', 'wrong_issuer')
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertFalse($guard->check());
    }

    public function testAuthUserInvalidToken(): void
    {
        $request = $this->generateRequest(['Authorization' => 'Bearer NOT_JWT_TOKEN']);

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
            'signer_key' => null,
            'signer_key_path' => null,
        ]);

        $this->expectException(EmptyConfigException::class);

        $this->expectExceptionMessage('One of required clerk config is empty.');

        app(ClerkGuard::class);
    }

    public function testAuthUserFromListedOriginLocalhostAllowed(): void
    {
        Config::set('clerk.allow_localhost', true);

        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                claims: ['azp' => 'https://app.example.com'],
            )
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertTrue($guard->check());
        $this->assertEquals('user_id', $guard->id());
    }

    public static function getLocalOrigins(): array
    {
        return [
            'localhost' => [
                'origin' => 'http://localhost:3071',
            ],
            'loopback address' => [
                'origin' => 'http://127.0.0.1:3070',
            ],
        ];
    }

    #[DataProvider('getLocalOrigins')]
    public function testAuthUserFromLocalOrigin(string $origin): void
    {
        Config::set('clerk.allow_localhost', true);

        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                claims: ['azp' => $origin],
            )
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertTrue($guard->check());
        $this->assertEquals('user_id', $guard->id());
    }

    public static function getRejectedOrigins(): array
    {
        return [
            'localhost inside a foreign domain' => [
                'origin' => 'http://localhost:3070.evil.com',
            ],
            'localhost as a subdomain' => [
                'origin' => 'http://localhost.evil.com:3070',
            ],
            'localhost over https' => [
                'origin' => 'https://localhost:3070',
            ],
            'localhost without port' => [
                'origin' => 'http://localhost',
            ],
            'localhost with trailing line break' => [
                'origin' => "http://localhost:3070\n",
            ],
            'foreign origin' => [
                'origin' => 'https://foreign.example.com',
            ],
            'not a string' => [
                'origin' => ['http://localhost:3071'],
            ],
        ];
    }

    #[DataProvider('getRejectedOrigins')]
    public function testAuthUserFromRejectedOrigin(string|array $origin): void
    {
        Config::set('clerk.allow_localhost', true);

        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                claims: ['azp' => $origin],
            )
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertFalse($guard->check());
    }

    public function testAuthUserFromLocalOriginLocalhostNotAllowed(): void
    {
        Config::set('clerk.allow_localhost', false);

        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                claims: ['azp' => 'http://localhost:3071'],
            )
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertFalse($guard->check());
    }

    public function testAuthUserFromLocalOriginInProduction(): void
    {
        Config::set('clerk.allow_localhost', true);

        $this->app->detectEnvironment(fn (): string => 'production');

        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                claims: ['azp' => 'http://localhost:3071'],
            )
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertFalse($guard->check());
    }

    public function testAuthUserFromLocalOriginWithoutLocalhostConfig(): void
    {
        $this->assertArrayNotHasKey('allow_localhost', config('clerk'));

        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                claims: ['azp' => 'http://localhost:3071'],
            )
            ->toString();

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertFalse($guard->check());
    }

    public function testAuthUserFromLocalOriginInvalidSignature(): void
    {
        Config::set('clerk.allow_localhost', true);

        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                claims: ['azp' => 'http://localhost:3071'],
            )
            ->toString();

        list($foreignSignerCert) = $this->generateCertificates();

        Config::set('clerk.signer_key', base64_encode($foreignSignerCert));

        $request = $this->generateRequest(['Authorization' => "Bearer {$clerkToken}"]);

        $guard = app(ClerkGuard::class)->setRequest($request);

        $this->assertFalse($guard->check());
    }
}
