<?php

namespace RonasIT\Clerk\Providers;

use App\Guards\ClerkGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ClerkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::extend(
            driver: 'clerk_session',
            callback: fn ($app) => app(ClerkGuard::class)->setRequest($app->make('request'))
        );

        $this->mergeConfigFrom(__DIR__ . '/../../config/clerk.php', 'clerk');
    }
}
