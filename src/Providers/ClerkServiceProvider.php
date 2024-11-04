<?php

namespace RonasIT\Clerk\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use RonasIT\Clerk\Auth\ClerkGuard;
use RonasIT\Clerk\Contracts\UserRepositoryContract;
use RonasIT\Clerk\Repositories\UserRepository;

class ClerkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::extend(
            driver: 'clerk_session',
            callback: fn ($app) => app(ClerkGuard::class)->setRequest($app->make('request'))
        );

        $this->app->bind(UserRepositoryContract::class, UserRepository::class);

        $this->mergeConfigFrom(__DIR__ . '/../../config/clerk.php', 'clerk');

        $this->publishes([
            __DIR__ . '/../../config/clerk.php' => config_path('clerk.php'),
        ], 'config');
    }
}
