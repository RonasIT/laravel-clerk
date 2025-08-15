<?php

namespace RonasIT\Clerk\Tests;

use RonasIT\Support\Traits\MockTrait;

class InstallCommandTest extends TestCase
{
    use MockTrait;

    public function testRun()
    {
        $authConfigPath = base_path('config/auth.php');

        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            [
                $this->functionCall('file_exists', [$authConfigPath], true),
                $this->functionCall('file_get_contents', [$authConfigPath], $this->getFixture('auth.php')),
                $this->functionCall('file_put_contents', [
                    $authConfigPath,
                    $this->getFixture('auth_after_changes.php'),
                ], $this->getFixture('auth_after_changes.php')),
            ]
        );   

        $this->artisan('laravel-clerk:install');
    }
}
