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
        // Ensure website URL is properly set
        if (empty($this->websiteUrl)) {
            $fail('The website URL must be set before validating the redirect URL.');
            return;
        }

        // Parse both URLs safely
        $websiteParts = parse_url($this->websiteUrl);
        $redirectParts = parse_url($value);

        // Ensure both URLs are valid
        if (!$websiteParts || !isset($websiteParts['host'])) {
            $fail('The website URL is invalid.');
            return;
        }

        if (!$redirectParts || !isset($redirectParts['host'])) {
            $fail('The redirect URL is invalid.');
            return;
        }

        // Normalize hosts to lowercase
        $websiteHost = strtolower($websiteParts['host']);
        $redirectHost = strtolower($redirectParts['host']);

        // Ensure redirect URL belongs to the same domain (or explicitly includes subdomains)
        if ($websiteHost !== $redirectHost) {
            if (!str_ends_with($redirectHost, '.' . $websiteHost)) {
                $fail('The redirect URL must belong to the same domain as the website URL.');
                return;
            }
        }

        // Ensure path validity
        $path = $redirectParts['path'] ?? '/';
        $path = rtrim($path, '/');

        // Block sensitive paths
        $blockedPaths = ['/admin', '/logout'];
        if (in_array($path, $blockedPaths)) {
            $fail('The redirect URL contains a restricted path.');
            return;
        }

        // Optional: Block query parameters that could manipulate behavior
        if (!empty($redirectParts['query'])) {
            $fail('The redirect URL must not contain query parameters.');
            return;
        }
    }
}
