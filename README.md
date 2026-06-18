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

## Configuration

Set the following environment variables to configure the package:

- `CLERK_ALLOWED_ISSUER` — the expected `iss` claim value of incoming JWT tokens.
- `CLERK_ALLOWED_ORIGINS` — comma-separated list of allowed `azp` claim values.
- `CLERK_SECRET_KEY` — your Clerk API secret key, used to verify the token signature.
- `CLERK_SIGNER_KEY` — base64-encoded PEM content of the public JWT key. Takes priority over `CLERK_SIGNER_KEY_PATH` when set.
- `CLERK_SIGNER_KEY_PATH` — path to the public JWT key file, relative to `base_path()`. Defaults to `clerk.pem`. Used as a fallback when `CLERK_SIGNER_KEY` is not set.

You can find the public JWT key in your Clerk dashboard under "Configure" → "API keys" → "JWKS Public Key".

Since a PEM block is multi-line, encode it with base64 before placing it into `CLERK_SIGNER_KEY`:

```sh
base64 < clerk.pem | tr -d '\n'
```

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

To test authenticated user requests guarded by `ClerkGuard`, use the `TokenMockTrait`:

1. Ensure clerk config is filled using `.env.testing` file or dynamically. The signer key is set automatically:

```php
Config::set('clerk', [
    'allowed_issuer' => 'issuer',
    'secret_key' => 'my_secret_key',
]);
```

2. Generate a JWT token and pass it to the `Authorization` header:

```php
use RonasIT\Clerk\Traits\TokenMockTrait;

class UserRepositoryTest extends TestCase
{
    use TokenMockTrait;
    
    public function test()
    {
        $clerkToken = $this
            ->createJWTToken(
                relatedTo: 'user_id',
                issuer: 'issuer',
            )
            ->toString();
            
        $this->withHeader('Authorization', "Bearer {$clerkToken}");     
    }
}
```

3. You may also pass custom claims to the token using `claims` parameter:

```php
    $clerkToken = $this
        ->createJWTToken(
            relatedTo: 'user_id',
            issuer: 'issuer',
            claims: [
                'email' => 'user@mail.com',
                'phone' => '+1234567789',
            ],
        )->toString();
```
