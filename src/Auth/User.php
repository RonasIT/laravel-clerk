<?php

namespace RonasIT\Clerk\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class User implements Authenticatable
{
    public function __construct(
        public readonly string $sub
    ) {
    }

    public function getAuthIdentifierName(): string
    {
        return 'sub';
    }

    public function getAuthIdentifier(): string
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPasswordName(): null
    {
        return null;
    }

    public function getAuthPassword(): null
    {
        return null;
    }

    public function getRememberToken(): null
    {
        return null;
    }

    public function setRememberToken($value): null
    {
        return null;
    }

    public function getRememberTokenName(): null
    {
        return null;
    }
}
