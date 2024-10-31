<?php

namespace RonasIT\Clerk\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Lcobucci\JWT\Token;

interface UserRepositoryContract
{
    public function fromToken(Token $token): Authenticatable;
}
