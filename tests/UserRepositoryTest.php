<?php

namespace RonasIT\Clerk\Tests;

use RonasIT\Clerk\Auth\User;
use RonasIT\Clerk\Repositories\UserRepository;
use RonasIT\Clerk\Tests\Support\TokenMockTrait;

class UserRepositoryTest extends TestCase
{
    use TokenMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->authRepository = app(UserRepository::class);
    }

    public function testClerkAuthRepositoryCreateUser()
    {
        $clerkToken = $this->createJWTToken(
            relatedTo: 'user_000000000000000000000000001',
        );

        $user = $this->authRepository->fromToken($clerkToken);

        $this->assertEquals(User::class, get_class($user));

        $this->assertEquals('user_000000000000000000000000001', $user->externalId);
    }
}