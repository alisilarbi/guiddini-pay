<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RedirectUrlRule implements ValidationRule
{
    private string $websiteUrl;

    public function __construct(string $websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (app()->environment() !== 'production') {
            return;
        }

        if (empty($this->websiteUrl)) {
            $fail('The website URL must be set before validating the redirect URL.');
            return;
        }

        $websiteParts = parse_url($this->websiteUrl);
        $redirectParts = parse_url($value);

        if (!$websiteParts || !isset($websiteParts['host'])) {
            $fail('The website URL is invalid.');
            return;
        }

        if (!$redirectParts || !isset($redirectParts['host'])) {
            $fail('The redirect URL is invalid.');
            return;
        }

        $websiteHost = strtolower($websiteParts['host']);
        $redirectHost = strtolower($redirectParts['host']);

        if ($websiteHost !== $redirectHost) {
            if (!str_ends_with($redirectHost, '.' . $websiteHost)) {
                $fail('The redirect URL must belong to the same domain as the website URL.');
                return;
            }
        }

        $path = $redirectParts['path'] ?? '/';
        $path = rtrim($path, '/');

        $blockedPaths = ['/admin', '/logout'];
        if (in_array($path, $blockedPaths)) {
            $fail('The redirect URL contains a restricted path.');
            return;
        }

        if (!empty($redirectParts['query'])) {
            $fail('The redirect URL must not contain query parameters.');
            return;
        }
    }
}
