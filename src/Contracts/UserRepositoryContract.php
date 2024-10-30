<?php

namespace RonasIT\Clerk\Contracts;

interface UserRepositoryContract
{
    public function fromToken(array $token): mixed;
}
