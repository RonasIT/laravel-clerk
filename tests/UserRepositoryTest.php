<?php

namespace RonasIT\Clerk\Tests;

use RonasIT\Clerk\Auth\User;
use RonasIT\Clerk\Repositories\UserRepository;
use RonasIT\Clerk\Tests\Support\TokenMockTrait;

class UserRepositoryTest extends TestCase
{
    use TokenMockTrait;

    protected UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = app(UserRepository::class);
    }

    public function testClerkAuthRepositoryCreateUser()
    {
        $clerkToken = $this->createJWTToken(
            relatedTo: 'user_000000000000000000000000001',
        );

        $user = $this->userRepository->fromToken($clerkToken);

        $this->assertEquals(User::class, get_class($user));

        $this->assertEquals('user_000000000000000000000000001', $user->externalId);
    }
}