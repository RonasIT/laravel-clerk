<?php

namespace App\Guards;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use RonasIT\Clerk\Contracts\UserRepositoryContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RonasIT\Clerk\Exceptions\EmptyConfigException;
use RonasIT\Clerk\Exceptions\TokenValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ClerkGuard implements Guard
{
    protected ?Authenticatable $user = null;
    protected Request $request;
    protected array $config;

    public function __construct()
    {
        $this->config = config('clerk');

        if (array_filter($this->config) !== $this->config) {
            throw new EmptyConfigException('One of required clerk config is empty.');
        }
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function check(): bool
    {
        return !is_null($this->user());
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?Authenticatable
    {
        if ($this->hasUser()) {
            return $this->user;
        }

        $token = $this->getToken();

        return (empty($token))
            ? null
            : $this->user = $this->retrieveByToken($token);
    }

    public function retrieveByToken(string $token): Authenticatable
    {
        try {
            $decoded = $this->decodeToken($token);
        } catch (TokenValidationException $e) {
            throw new UnauthorizedHttpException('clerk', $e->getMessage());
        }

        $user = app(UserRepositoryContract::class)->fromToken($decoded);

        $this->setUser($user);

        return $this->user();
    }

    public function decodeToken(string $sessionToken): Token
    {
        $parser = new Parser(new JoseEncoder());

        $decoded = $parser->parse($sessionToken);

        $this->validateToken($decoded);

        return $decoded;
    }

    public function getToken(): ?string
    {
        return $this->request->bearerToken();
    }

    public function id(): ?string
    {
        return $this->user()->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        try {
            $this->retrieveByToken(head($credentials));
        } catch (TokenValidationException $e) {
            return false;
        }

        return true;
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function hasUser(): bool
    {
        return !empty($this->user);
    }

    protected function validateToken(Token $decoded): void
    {
        $now = Carbon::now();

        if ($decoded->isExpired($now) || !$decoded->hasBeenIssuedBefore($now)) {
            throw new TokenValidationException('Token is expired or not yet valid');
        }

        if ($decoded->hasBeenIssuedBy(config('clerk.allowed_issuers'))) {
            throw new TokenValidationException('Token was issued by not allowed issuer.');
        }

        if (!in_array($decoded->claims()->get('azp'), config('clerk.allowed_origins'))) {
            throw new TokenValidationException('Token was received from not allowed origin.');
        }

        $signerKey = InMemory::file(base_path(config('clerk.signer_key_path')), config('clerk.secret_key'));

        $isValid = (new Validator())->validate(
            $decoded,
            new SignedWith(new Sha256(), $signerKey),
        );

        if (!$isValid) {
            throw new TokenValidationException('Token signature verification failed.');
        }
    }
}
