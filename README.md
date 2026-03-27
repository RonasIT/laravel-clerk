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
use RonasIT\Clerk\Contracts\UserRepositoryContract;
use App\Support\Clerk\MyAwesomeUserRepository;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(UserRepositoryContract::class, MyAwesomeUserRepository::class);
    }
}
```

## Testing

The package provides `TokenMockTrait` to help you write tests without a real Clerk instance. It generates RSA-signed JWTs that the `ClerkGuard` will accept.

### Setup

Add `TokenMockTrait` to your test class:

> **Note:** `createJWTToken()` writes the generated RSA public key to `base_path(SIGNER_KEY_PATH)` (default: `tests/public_key.pem`) so the guard can verify tokens. Ensure this path is writable, or override the `SIGNER_KEY_PATH` constant in your test class to use a different location.

### Generating tokens

Call `createJWTToken()` to produce a signed JWT. The first argument is the subject (`sub` claim), which becomes the `external_id` on the resolved `User` object:

```php
$token = $this->createJWTToken('user_123')->toString();
```

Pass additional Clerk claims as the third argument:

```php
$token = $this->createJWTToken(
    relatedTo: 'user_123',
    claims: ['email' => 'user@example.com', 'role' => 'admin'],
)->toString();
```

### Making authenticated requests

Use the generated token as a Bearer token in your HTTP test requests:

```php
$response = $this->withToken($token)->getJson('/api/protected-endpoint');
```
