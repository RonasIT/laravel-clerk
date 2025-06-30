<?php

namespace RonasIT\Clerk\Tests;

use Orchestra\Testbench\TestCase as BaseTest;
use RonasIT\Clerk\Providers\ClerkServiceProvider;

class TestCase extends BaseTest
{
    protected function getPackageProviders($app): array
    {
        return [
            ClerkServiceProvider::class,
        ];
    }
}
