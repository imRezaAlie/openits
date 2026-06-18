<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    |
    | Environment-backed defaults for settings that can be overridden in the
    | database via the admin panel.
    |
    */

    'google_login_enabled' => env('GOOGLE_LOGIN_ENABLED', false),

    'ldap_login_enabled' => env('LDAP_LOGIN_ENABLED', false),

    'ldap_server' => env('LDAP_SERVER'),

    'ldap_port' => env('LDAP_PORT', 389),

    'ldap_base_dn' => env('LDAP_BASE_DN'),

    'ldap_domain' => env('LDAP_DOMAIN'),

];
