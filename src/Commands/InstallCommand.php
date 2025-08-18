<?php

namespace RonasIT\Clerk\Commands;

use Illuminate\Console\Command;
use Winter\LaravelConfigWriter\ArrayFile;

class InstallCommand extends Command
{
    protected $signature = 'laravel-clerk:install';

    protected $description = 'Installs Clerk authentication driver';

    public function handle(): void
    {
        $config = ArrayFile::open(base_path('config/auth.php'));
        
        $config
            ->set('defaults.guard', 'clerk')
            ->set('defaults.passwords', 'users')
            ->set('guards.clerk.driver', 'clerk_session')
            ->set('guards.clerk.provider', 'users');

        $config->write();
    }
}
