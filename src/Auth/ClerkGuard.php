<?php

namespace RonasIT\Clerk\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use RonasIT\Clerk\Contracts\UserRepositoryContract;
use RonasIT\Clerk\Exceptions\EmptyConfigException;

class ClerkGuard implements Guard
{
    protected ?Authenticatable $user = null;
    protected Request $request;
    protected array $config;

    public function __construct()
    {
        $this->config = config('clerk');

        $this->validateConfigs();
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

    public function retrieveByToken(string $token): ?Authenticatable
    {
        try {
            $decoded = $this->decodeToken($token);
        } catch (CannotDecodeContent|InvalidTokenStructure) {
            return null;
        }

        if (!$this->isValidToken($decoded)) {
            return null;
        }

        $user = app(UserRepositoryContract::class)->fromToken($decoded);

        $this->setUser($user);

        return $this->user();
    }

    public function decodeToken(string $sessionToken): Token
    {
        $parser = new Parser(new JoseEncoder());

        return $parser->parse($sessionToken);
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
        $user = $this->retrieveByToken(head($credentials));

        return !is_null($user);
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function hasUser(): bool
    {
        return !empty($this->user);
    }

    protected function isValidToken(Token $decoded): bool
    {
        $now = Carbon::now();

        $origin = $decoded->claims()->get('azp');

        return !$decoded->isExpired($now)
            && $decoded->hasBeenIssuedBefore($now)
            && $decoded->hasBeenIssuedBy(config('clerk.allowed_issuer'))
            && (empty($origin) || in_array($origin, config('clerk.allowed_origins')))
            && $this->hasValidSignature($decoded);
    }

    protected function hasValidSignature(Token $decoded): bool
    {
        $signerKey = InMemory::file(base_path(config('clerk.signer_key_path')), config('clerk.secret_key'));

        return (new Validator())->validate(
            $decoded,
            new SignedWith(new Sha256(), $signerKey),
        );
    }

    protected function validateConfigs(): void
    {
        $requiredConfigs = Arr::only($this->config, [
            'allowed_issuer',
            'secret_key',
            'signer_key_path',
        ]);

        if (array_filter($requiredConfigs) !== $requiredConfigs) {
            throw new EmptyConfigException('One of required clerk config is empty.');
        }
    }
}
