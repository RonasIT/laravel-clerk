# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
docker compose exec nginx vendor/bin/phpunit

# Run a single test file
docker compose exec nginx vendor/bin/phpunit tests/ClerkGuardTest.php

# Run a single test method
docker compose exec nginx vendor/bin/phpunit --filter testMethodName

# Check code style (no changes)
docker compose exec nginx vendor/bin/pint --test

# Fix code style
docker compose exec nginx vendor/bin/pint
```

## Architecture

This is a Laravel authentication package that integrates [Clerk](https://clerk.com/) JWT-based session tokens as a custom Laravel auth guard.

### Authentication Flow

1. `ClerkGuard` extracts a bearer token from the HTTP `Authorization` header
2. Parses the JWT using `lcobucci/jwt` (without validating first)
3. Validates the token: expiry, issue time, issuer (`allowed_issuer`), origin (`azp` claim vs `allowed_origins`), and RSA signature (public key from `clerk.pem`)
4. Passes the validated JWT to `UserRepositoryContract::createFromToken()` to produce an `Authenticatable` user
5. User is cached on the guard instance to avoid repeated validation

### Extensibility

The `UserRepositoryContract` is the primary extension point — applications bind their own implementation to customize how a Clerk JWT maps to an application user (e.g., looking up by `sub` claim in a local database). The default `UserRepository` creates a simple `User` object with `externalId` set to the `sub` claim.

### Configuration

Three required config values (via `config/clerk.php`):
- `allowed_issuer` — Clerk Frontend API URL (env `CLERK_ALLOWED_ISSUER`)
- `secret_key` — Secret key (env `CLERK_SECRET_KEY`)
- `signer_key_path` — Path to RSA public key file, defaults to `clerk.pem` (env `CLERK_SIGNER_KEY_PATH`)

Optional: `allowed_origins` — comma-separated list for `azp` claim validation.

### Testing

Tests use Orchestra Testbench. `TokenMockTrait` (in `src/Traits/`) generates mock JWTs with RSA key pairs for testing without a real Clerk instance. Test fixtures live in `tests/fixtures/`.