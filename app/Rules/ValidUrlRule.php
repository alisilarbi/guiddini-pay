<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUrlRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $urlParts = parse_url($value);

        if (!$urlParts || empty($urlParts['scheme']) || empty($urlParts['host'])) {
            $fail('The :attribute must be a valid URL.');
            return;
        }

        $scheme = strtolower($urlParts['scheme']);
        $host = strtolower($urlParts['host']);

        if ($scheme !== 'https') {
            $fail('The :attribute must use HTTPS.');
            return;
        }

        if (!empty($urlParts['port'])) {
            $fail('The :attribute must not contain a port.');
            return;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $fail('The :attribute must not be an IP address.');
            return;
        }

        $blockedHosts = ['localhost', '127.0.0.1', '::1'];
        if (in_array($host, $blockedHosts)) {
            $fail('The :attribute must not be a reserved host.');
            return;
        }

        if ($this->isInternalIP($host)) {
            $fail('The :attribute must not be an internal network IP.');
            return;
        }

        $path = $urlParts['path'] ?? '/';
        $path = rtrim($path, '/');

        $blockedPaths = ['/admin', '/logout'];
        if (in_array($path, $blockedPaths)) {
            $fail('The :attribute contains a restricted path.');
            return;
        }
    }

    private function isInternalIP(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE) === false;
    }

}
