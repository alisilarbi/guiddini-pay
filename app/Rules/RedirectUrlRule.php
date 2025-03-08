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
        $websiteParts = parse_url($this->websiteUrl);
        $redirectParts = parse_url($value);

        if (!$websiteParts || !$redirectParts) {
            $fail('Invalid URL format.');
            return;
        }

        $websiteHost = strtolower($websiteParts['host']);
        $redirectHost = strtolower($redirectParts['host']);

        if ($websiteHost !== $redirectHost) {
            if (!str_ends_with($redirectHost, '.' . $websiteHost)) {
                $fail('The redirect URL must belong to the same host.');
                return;
            }
        }

        $path = $redirectParts['path'] ?? '/';
        $path = rtrim($path, '/');

        if (in_array($path, ['/admin', '/logout'])) {
            $fail('The redirect URL contains a restricted path.');
            return;
        }

        if (!empty($redirectParts['query'])) {
            $fail('The redirect URL must not contain query parameters.');
            return;
        }
    }
}
