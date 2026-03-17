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

2. Run package's `install` command

```sh
php artisan laravel-clerk:install
```

3. Populate the necessary configuration options in `config/clerk.php`.

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

## Testing

To test authenticated user requests guarded by `ClerkGuard`, use the `TokenMockTrait`:
1. set the Clerk configuration values for the test:

```php
Config::set('clerk', [
    'allowed_issuer' => 'issuer',
    'secret_key' => self::SECRET_KEY_PASS,
    'signer_key_path' => self::SIGNER_KEY_PATH,
]);
```

2. create a JWT token using the `createJWTToken` method, you may also pass custom claims to the token:

```php
$clerkToken = $this
    ->createJWTToken(
        relatedTo: 'user_id',
        issuer: 'issuer',
        claims: ['email' => 'user@mail.com'],
    )
    ->toString();
```

3. attach generated token to the request’s `Authorization` header as a Bearer token.
