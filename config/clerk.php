<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Token issuer, url
    |--------------------------------------------------------------------------
    | You can find your Clerk Frontend API URL in your Clerk dashboard under:
    | "Configure" -> "API keys" -> "Frontend API URL"
    */
    'allowed_issuer' => env('CLERK_ALLOWED_ISSUER'),

    /*
    |--------------------------------------------------------------------------
    | Token origin, OPTIONAL list of URLs separated by ","
    |--------------------------------------------------------------------------
    | Your client app origins, are highly recommended to set when using Web client applications.
    */
    'allowed_origins' => explode(',', env('CLERK_ALLOWED_ORIGINS', '')),

    /*
    |--------------------------------------------------------------------------
    | Secret key, string
    |--------------------------------------------------------------------------
    | Your API secret key, needed to verify the token signature, can be found
    | in your Clerk dashboard under:
    | "Configure" -> "API keys" -> "Secret keys"
    */
    'secret_key' => env('CLERK_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Public key path, filepath starting from base_path
    |--------------------------------------------------------------------------
    | Path to your public JWT key file. You can find the file content in your
    | Clerk dashboard under:
    | "Configure" -> "API keys" -> "JWKS Public Key""
    */
    'signer_key_path' => env('CLERK_SIGNER_KEY_PATH', 'clerk.pem'),
];
