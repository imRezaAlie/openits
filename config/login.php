<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Login Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Protects email/password, LDAP, and Google token login from brute force.
    | Per-credential limits apply per identifier + IP. IP limits slow username
    | spraying across many accounts from a single address.
    |
    */

    'max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),

    'decay_minutes' => (int) env('LOGIN_DECAY_MINUTES', 1),

    'ip_max_attempts' => (int) env('LOGIN_IP_MAX_ATTEMPTS', 20),

    /*
    |--------------------------------------------------------------------------
    | Demo Credentials on Login Page
    |--------------------------------------------------------------------------
    |
    | When enabled, the login page shows the seeded admin email/password and a
    | "Fill form" button. Keep false in production.
    |
    */

    'show_demo_credentials' => (bool) env('LOGIN_SHOW_DEMO_CREDENTIALS', false),

];
