<?php

namespace App\Actions\Auth;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class ResolveMicrosoftEmailAction
{
    /**
     * Resolve the email address from Microsoft OAuth user data (handling guest/external #EXT# accounts and fallback attributes).
     */
    public function execute(SocialiteUser $microsoftUser): ?string
    {
        $microsoftRawUser = $microsoftUser->user ?? [];
        $rawMail = $microsoftRawUser['mail'] ?? null;
        $userPrincipalName = $microsoftRawUser['userPrincipalName'] ?? ($microsoftUser->getEmail() ?? '');

        // 1. Prefer official 'mail' attribute if valid email
        if (! empty($rawMail) && filter_var($rawMail, FILTER_VALIDATE_EMAIL) && ! str_contains($rawMail, '#EXT#')) {
            return $rawMail;
        }

        // 2. Parse guest/external #EXT# format from userPrincipalName or email
        $target = ! empty($userPrincipalName) ? $userPrincipalName : $microsoftUser->getEmail();
        if (! empty($target) && str_contains($target, '#EXT#')) {
            $prefix = explode('#EXT#', $target)[0];
            $lastUnderscorePos = strrpos($prefix, '_');
            if ($lastUnderscorePos !== false) {
                return substr_replace($prefix, '@', $lastUnderscorePos, 1);
            }
        }

        // 3. Fallback to Socialite email
        return $microsoftUser->getEmail();
    }
}
