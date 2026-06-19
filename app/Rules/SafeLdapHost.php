<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeLdapHost implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail(__('validation.regex', ['attribute' => $attribute]));

            return;
        }

        $host = strtolower(trim($value));

        if (str_contains($host, '://')) {
            $parsed = parse_url($host, PHP_URL_HOST);

            if (! is_string($parsed) || $parsed === '') {
                $fail('The :attribute must be a valid hostname or IP address.');

                return;
            }

            $host = strtolower($parsed);
        }

        if ($this->isBlockedHost($host)) {
            $fail('The :attribute must not point to a private or reserved network address.');
        }
    }

    protected function isBlockedHost(string $host): bool
    {
        $blockedPatterns = [
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            '::1',
            'metadata.google.internal',
        ];

        if (in_array($host, $blockedPatterns, true)) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return ! filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        if (str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return true;
        }

        $resolved = gethostbyname($host);

        if ($resolved !== $host && filter_var($resolved, FILTER_VALIDATE_IP)) {
            return ! filter_var(
                $resolved,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        return false;
    }
}
