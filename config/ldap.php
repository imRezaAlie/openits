<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LDAP Connection Defaults
    |--------------------------------------------------------------------------
    |
    | Environment-backed defaults for LDAP settings. Values stored in the
    | database via the admin panel take precedence when configured.
    |
    */

    'login_enabled' => env('LDAP_LOGIN_ENABLED', false),

    'server' => env('LDAP_SERVER'),

    'port' => (int) env('LDAP_PORT', 389),

    'base_dn' => env('LDAP_BASE_DN'),

    'domain' => env('LDAP_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    'use_ssl' => env('LDAP_USE_SSL', false),

    'use_starttls' => env('LDAP_USE_STARTTLS', false),

    'allow_insecure' => env('LDAP_ALLOW_INSECURE', false),

    /*
    |--------------------------------------------------------------------------
    | Service Account (used for sync and pre-bind search)
    |--------------------------------------------------------------------------
    */

    'bind_dn' => env('LDAP_BIND_DN'),

    'bind_password' => env('LDAP_BIND_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Directory Type & Filters
    |--------------------------------------------------------------------------
    */

    'type' => env('LDAP_TYPE', 'ad'), // ad | openldap

    'user_filter' => env('LDAP_USER_FILTER'), // null = auto based on type

    'sync_filter' => env('LDAP_SYNC_FILTER'), // null = auto based on type

    'username_attribute' => env('LDAP_USERNAME_ATTRIBUTE'), // null = auto

    'attributes' => [
        'samaccountname',
        'displayname',
        'mail',
        'distinguishedname',
        'memberof',
        'uid',
        'cn',
    ],

    /*
    |--------------------------------------------------------------------------
    | Behaviour
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('LDAP_TIMEOUT', 5),

    'fallback_to_local' => env('LDAP_FALLBACK_TO_LOCAL', true),

    'auto_provision' => env('LDAP_AUTO_PROVISION', false),

    'allow_email_linking' => env('LDAP_ALLOW_EMAIL_LINKING', false),

    'allowed_groups' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('LDAP_ALLOWED_GROUPS', ''))
    ))),

    'sync_page_size' => (int) env('LDAP_SYNC_PAGE_SIZE', 500),

    /*
    |--------------------------------------------------------------------------
    | Optional group → role mapping
    |--------------------------------------------------------------------------
    |
    | Example: ['CN=Admins,OU=Groups,DC=example,DC=com' => 'admin']
    |
    */

    'group_role_mapping' => json_decode((string) env('LDAP_GROUP_ROLE_MAPPING', '{}'), true) ?: [],

    'domains' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('LDAP_DOMAINS', env('LDAP_DOMAIN', '')))
    ))),

];
