<?php

namespace RonasIT\Clerk\Providers;

use App\Guards\ClerkGuard;
use Illuminate\Support\Facades\Auth;

class ClerkServiceProvider
{
    public function boot(): void
    {
        Auth::extend(
            driver: 'clerk_session',
            callback: fn ($app) => app(ClerkGuard::class)->setRequest($app->make('request'))
        );
    }
}
