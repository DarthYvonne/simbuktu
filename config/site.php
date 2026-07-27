<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Adgangskode til det offentlige site
    |--------------------------------------------------------------------------
    |
    | Mens sitet bygges, ligger det bag HTTP Basic. Sæt SITE_AUTH_ENABLED=false
    | i .env, når det skal være offentligt. Brugernavn og kodeord bør sættes i
    | .env — værdierne herunder er kun en fallback, så porten aldrig står åben
    | ved et uheld.
    |
    */

    'auth' => [
        'enabled'  => filter_var(env('SITE_AUTH_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'user'     => env('SITE_AUTH_USER', 'anders'),
        'password' => env('SITE_AUTH_PASSWORD', 'numsefisk'),
    ],

];
