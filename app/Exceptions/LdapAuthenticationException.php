<?php

namespace App\Exceptions;

use Exception;

class LdapAuthenticationException extends Exception
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'ldap_error',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
