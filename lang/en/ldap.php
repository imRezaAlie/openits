<?php

return [

    'button' => [
        'sign_in_with_ldap' => 'Sign in with LDAP',
        'test_connection' => 'Test Connection',
        'sync_users' => 'Sync Users',
    ],

    'form' => [
        'username' => 'Username',
        'password' => 'Password',
        'domain' => 'Domain',
        'select_domain' => 'Select domain',
    ],

    'settings' => [
        'title' => 'LDAP Settings',
        'ldap_login' => 'LDAP Login',
        'ldap_login_help' => 'Allow users to sign in with their Active Directory or OpenLDAP credentials.',
        'credentials_warning' => 'LDAP connection settings are incomplete. Configure server, port, base DN, and domain before enabling this feature.',
        'credentials_ok' => 'LDAP connection settings are configured.',
        'server' => 'LDAP Server',
        'port' => 'Port',
        'base_dn' => 'Base DN',
        'domain' => 'Domain',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'save' => 'Save',
        'saving' => 'Saving…',
        'testing' => 'Testing…',
        'syncing' => 'Syncing…',
        'back_to_settings' => 'Back to Settings',
    ],

    'messages' => [
        'enabled' => 'LDAP login has been enabled.',
        'disabled' => 'LDAP login has been disabled.',
        'login_success' => 'Signed in with LDAP successfully.',
        'connection_success' => 'LDAP connection test succeeded.',
        'sync_started' => 'LDAP user sync has been queued.',
        'sync_completed' => 'LDAP user sync completed. :count user(s) processed.',
        'settings_saved' => 'LDAP settings have been saved.',
    ],

    'errors' => [
        'disabled' => 'LDAP login is currently disabled.',
        'invalid_credentials' => 'Invalid LDAP username or password.',
        'user_not_found' => 'LDAP user was not found in the directory.',
        'connection_failed' => 'Unable to connect to the LDAP server.',
        'bind_failed' => 'LDAP bind failed. Check credentials and configuration.',
        'search_failed' => 'LDAP search failed.',
        'starttls_failed' => 'LDAP STARTTLS negotiation failed.',
        'extension_missing' => 'The PHP LDAP extension is not installed.',
        'credentials_missing' => 'LDAP server, port, base DN, and domain must be configured before enabling LDAP login.',
        'server_unreachable' => 'LDAP server is unreachable. Please try again or use local login.',
        'rate_limited' => 'Too many LDAP login attempts. Please try again later.',
        'sync_failed' => 'LDAP user sync failed.',
        'unauthorized' => 'You are not authorized to manage LDAP settings.',
        'not_provisioned' => 'Your LDAP account is not authorized to access this application.',
    ],

];
