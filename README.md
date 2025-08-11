[![Coverage Status](https://coveralls.io/repos/github/RonasIT/laravel-clerk/badge.svg?branch=main)](https://coveralls.io/github/RonasIT/laravel-clerk?branch=main)

# Laravel Clerk Guard

## Introduction

This package offers an authentication guard to seamlessly integrate [Clerk](https://clerk.com) authentication into your
Laravel project.

## Installation

1. Use Composer to install the package:

```sh
composer require ronasit/laravel-clerk
```

2. Publish the package configuration:

```sh
php artisan vendor:publish --provider=RonasIT\\Clerk\\Providers\\ClerkServiceProvider
```

3. Add a new `clerk` guard within the `guards` array in your `config/auth.php` file:

```php
return [
    'defaults' => [
        'guard' => 'clerk',
        'passwords' => 'users',
    ],

    'guards' => [
        'clerk' => [
            'driver' => 'clerk_session',
            'provider' => 'users',
        ],
        // Other guards...
    ],
```

4. Populate the necessary configuration options in `config/clerk.php`.

## Usage

By default, your app returns the `User` class with just the `external_id` property, which holds the user's ID in Clerk.

To customize this behavior, you'll need to create your own `UserRepository` that implements the `UserRepositoryContract`.
Then, rebind it in one of the service providers:

```php
use RonasIT\Clerk\Contracts\ClerkUserRepositoryContract;
use App\Support\Clerk\MyAwesomeUserRepository;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ClerkUserRepositoryContract::class, MyAwesomeUserRepository::class);
    }
}
```
