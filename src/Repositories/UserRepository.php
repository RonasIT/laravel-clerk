<?php

namespace RonasIT\Clerk\Repositories;

use RonasIT\Clerk\Auth\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Lcobucci\JWT\Token;
use RonasIT\Clerk\Contracts\UserRepositoryContract;

class ClerkUserRepository implements UserRepositoryContract
{
    public function fromToken(Token $token): Authenticatable
    {
        return new User($token->claims()->get('sub'));
    }
}
