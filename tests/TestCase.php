<?php

namespace RonasIT\Clerk\Tests;

use Orchestra\Testbench\TestCase as BaseTest;
use RonasIT\Clerk\Providers\ClerkServiceProvider;
use RonasIT\Support\Traits\FixturesTrait;

class TestCase extends BaseTest
{
    use FixturesTrait;

    protected function defineEnvironment($app): void
    {
        $app->setBasePath(__DIR__ . '/..');
    }

    protected function getPackageProviders($app): array
    {
        return [
            ClerkServiceProvider::class,
        ];
    }
}
