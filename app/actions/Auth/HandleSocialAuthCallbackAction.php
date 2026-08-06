<?php

namespace App\Actions\Auth;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class HandleSocialAuthCallbackAction
{
    /**
     * Handle the OAuth user details for a given provider (Google, Microsoft, etc.)
     * and return an API token.
     */
    public function execute(SocialiteUser $socialUser, AuthProvider $provider, ?string $resolvedEmail = null): string
    {
        $email = $resolvedEmail ?? $socialUser->getEmail();

        return DB::transaction(function () use ($socialUser, $provider, $email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'image' => $socialUser->getAvatar() ?? $user->image,
                ]);
            } else {
                $defaultName = match ($provider) {
                    AuthProvider::GOOGLE => 'Google User',
                    AuthProvider::MICROSOFT => 'Microsoft User',
                    default => 'Social User',
                };

                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $defaultName,
                    'username' => $this->generateUniqueUsername($socialUser->getName() ?? $email),
                    'email' => $email,
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'image' => $socialUser->getAvatar(),
                    'role' => UserRole::CUSTOMER,
                    'password' => null,
                    'email_verified_at' => now(),
                ]);
            }

            return $user->createToken('api_token')->plainTextToken;
        });
    }

    /**
     * Generate a clean, unique username from user name or email.
     */
    private function generateUniqueUsername(string $nameOrEmail): string
    {
        $base = Str::slug(explode('@', $nameOrEmail)[0], '');
        if (empty($base)) {
            $base = 'user';
        }

        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }
}
