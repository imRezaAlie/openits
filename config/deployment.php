<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deployment Route
    |--------------------------------------------------------------------------
    |
    | Remote migrate/seed via HTTP is disabled by default. Set enabled to true
    | only during initial setup, then turn it off again.
    |
    */

    'enabled' => env('DEPLOYMENT_ENABLED', false),

    'token' => env('DEPLOYMENT_TOKEN'),

];
