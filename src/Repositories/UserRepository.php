<?php

namespace RonasIT\Clerk\Repositories;

use RonasIT\Clerk\Auth\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Lcobucci\JWT\Token;
use RonasIT\Clerk\Contracts\UserRepositoryContract;

class UserRepository implements UserRepositoryContract
{
    public function fromToken(Token $token): Authenticatable
    {
        $externalId = $token->claims()->get('sub');

        return new User($externalId);
    }
}
