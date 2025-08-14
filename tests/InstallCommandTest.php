<?php

namespace RonasIT\Clerk\Tests;

use RonasIT\Support\Traits\MockTrait;

class InstallCommandTest extends TestCase
{
    use MockTrait;

    public function testRun()
    {
        $this->mockNativeFunction(
            '\Winter\LaravelConfigWriter',
            [
                [
                    'function' => 'file_put_contents',
                    'arguments' => [base_path('config/auth.php'), $this->getFixture('auth.php')],
                    'result' => $this->getFixture('auth.php'),
                ],
            ]
        );   

        $this->artisan('laravel-clerk:install');
    }
}
