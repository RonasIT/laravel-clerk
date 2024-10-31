# Laravel Clerk Guard

## Introduction

Package provides auth guard to easy configure authentication using [Clerk](https://clerk.com).

## Installation

1. Install the package using the following command: `composer require ronasit/laravel-clerk`
1. Run `php artisan vendor:publish --provider=RonasIT\\Clerk\\ClerkServiceProvider`
1. Add new `clerk` guard to the `guards` list of your `config/auth.php`

```php
//auth.php

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
        ...
    ],
```

4. Fill required configs in `config/clerk.php`
