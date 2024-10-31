<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Token issuer, url
    |--------------------------------------------------------------------------
    | You Clerk Frontend API URL, can be gotten in you clerk dashboard in:
    | "Configure" -> "API keys" -> "Show API URLs" -> "Frontend API URL"
    */
    'allowed_issuer' => env('CLERK_ALLOWED_ISSUER'),

    /*
    |--------------------------------------------------------------------------
    | Token origin, list of URLs separated by ","
    |--------------------------------------------------------------------------
    | Your client apps origins
    */
    'allowed_origins' => explode(',', env('CLERK_ALLOWED_ORIGINS')),

    /*
    |--------------------------------------------------------------------------
    | Secret key, string
    |--------------------------------------------------------------------------
    | Your API secret key, required to check token signature, can be gotten in
    | you clerk dashboard in:
    | "Configure" -> "API keys" -> "Secret keys"
    */
    'secret_key' => env('CLERK_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Public key path, filepath starting from base_path
    |--------------------------------------------------------------------------
    | Path to your public JWT key file. File content can be gotten in you clerk
    | dashboard in:
    | "Configure" -> "API keys" -> "Show JWT public key" -> "PEM Public Key"
    */
    'signer_key_path' => env('CLERK_SIGNER_KEY_PATH', 'clerk.pem'),
];
