<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Authentication Language Lines
    |--------------------------------------------------------------------------
    */

    'button' => [
        'sign_in_with_google' => 'Sign in with Google',
    ],

    'settings' => [
        'title' => 'Settings',
        'google_login' => 'Google Login',
        'google_login_help' => 'Allow users to sign in with their Google account.',
        'credentials_warning' => 'Google OAuth credentials are not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI in your environment before enabling this feature.',
        'credentials_ok' => 'Google OAuth credentials are configured.',
        'redirect_uri' => 'Redirect URI',
        'client_id' => 'Client ID',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'save' => 'Save',
        'saving' => 'Saving…',
    ],

    'messages' => [
        'enabled' => 'Google login has been enabled.',
        'disabled' => 'Google login has been disabled.',
        'login_success' => 'Signed in with Google successfully.',
        'account_linked' => 'Your Google account has been linked.',
    ],

    'errors' => [
        'disabled' => 'Google login is currently disabled.',
        'oauth_failed' => 'Google sign-in failed. Please try again or use email and password.',
        'email_missing' => 'Google did not provide an email address for this account.',
        'credentials_missing' => 'Google OAuth credentials must be configured before enabling Google login.',
        'unauthorized' => 'You are not authorized to manage settings.',
    ],

];
