<?php

namespace App\Guards;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use RonasIT\Clerk\Contracts\UserRepositoryContract;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ClerkGuard implements Guard
{
    protected $user = null;
    protected Request $request;

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

        return empty($token = $this->getToken())
            ? null
            : $this->user = $this->retrieveByToken($token);
    }

    public function retrieveByToken(string $token): mixed
    {
        try {
            $payload = $this->getPayloadFromUserSessionToken($token);
        } catch (Exception $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        }

        return app(UserRepositoryContract::class)->fromToken($payload);
    }

    public function getPayloadFromUserSessionToken(string $sessionToken): array
    {
        $parser = new Parser(new JoseEncoder());

        $decoded = $parser->parse($sessionToken);

        $now = Carbon::now();

        if ($decoded->isExpired($now) || !$decoded->hasBeenIssuedBefore($now)) {
            throw new Exception('Token is expired or not yet valid');
        }

        if (isset($decoded->azp)) {
            if (!in_array($decoded->azp, config('clerk.allowed_origins', []))) {
                throw new Exception('Invalid azp claim');
            }
        }

        return (array) $decoded;
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
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->setUser($user);

            return true;
        } else {
            return false;
        }
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function hasUser(): bool
    {
        return !is_null($this->user);
    }
}
