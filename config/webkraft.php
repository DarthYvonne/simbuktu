<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin URL prefix
    |--------------------------------------------------------------------------
    | Where the Webkraft admin lives. Default: yoursite.com/cms
    */
    'path' => env('WEBKRAFT_PATH', 'cms'),

    /*
    |--------------------------------------------------------------------------
    | Admin middleware
    |--------------------------------------------------------------------------
    | Applied to every admin route. The host app should add its own admin
    | guard here (e.g. 'admin') so only the right users reach the CMS.
    | simbuktu sets: ['web', 'auth', 'admin'].
    */
    'middleware' => ['web', 'auth', 'admin'],

    /*
    |--------------------------------------------------------------------------
    | Public page rendering
    |--------------------------------------------------------------------------
    | When true, Webkraft registers a fallback route that renders published
    | pages by slug (and the home page at /). Turn off if the host renders
    | pages itself.
    */
    'public_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Media archive storage
    |--------------------------------------------------------------------------
    | Filesystem disk + base folder for uploaded images/videos.
    */
    'media_disk' => env('WEBKRAFT_MEDIA_DISK', 'public'),
    'media_path' => 'webkraft',

    /*
    |--------------------------------------------------------------------------
    | Branding defaults
    |--------------------------------------------------------------------------
    | Starting values for the public theme. These can be overridden per-site
    | in the Settings UI once that's built.
    */
    'brand' => [
        'name'      => env('APP_NAME', 'Webkraft'),
        'logo'      => null,        // media path or URL
        'primary'   => '#4f46e5',   // accent color
        'container' => '72rem',     // max content width
    ],

];
