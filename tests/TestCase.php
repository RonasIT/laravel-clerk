<?php

namespace RonasIT\Clerk\Tests;

use RonasIT\Clerk\Providers\ClerkServiceProvider;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function defineEnvironment($app): void
    {
        $app->setBasePath(__DIR__ . '/..');
    }

    protected function getPackageProviders(): array
    {
        return [
            ClerkServiceProvider::class,
        ];
    }
}
