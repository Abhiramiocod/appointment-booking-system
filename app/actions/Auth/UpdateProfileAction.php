<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfileAction
{
    public function execute(User $user, array $validated): User
    {
        $this->handleAvatar($user, $validated);

        // Prevent OAuth users from changing their email
        if (in_array($this->getProvider($user), ['google', 'microsoft'])) {
            unset($validated['email']);
        }

        // Remove avatar fields before updating user
        unset($validated['avatar'], $validated['remove_avatar']);

        $user->update($validated);

        if ($user->isStaff()) {
            $this->updateStaffProfile($user, $validated);
        }

        return $user->fresh([
            'staffProfile.designation',
        ]);
    }

    private function handleAvatar(User $user, array &$validated): void
    {
        if (! empty($validated['remove_avatar'])) {

            if (
                $user->image &&
                ! str_starts_with($user->image, 'http') &&
                Storage::disk('public')->exists($user->image)
            ) {
                Storage::disk('public')->delete($user->image);
            }

            $user->image = null;
            $user->save();

            return;
        }

        if (
            isset($validated['avatar']) &&
            $validated['avatar'] instanceof UploadedFile
        ) {

            if (
                $user->image &&
                ! str_starts_with($user->image, 'http') &&
                Storage::disk('public')->exists($user->image)
            ) {
                Storage::disk('public')->delete($user->image);
            }

            $path = $validated['avatar']->store('avatars', 'public');

            $user->image = $path;
            $user->save();
        }
    }

    private function updateStaffProfile(User $user, array $validated): void
    {
        $staffProfile = $user->staffProfile()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $staffProfile->update(array_filter([
            'phone' => $validated['phone'] ?? $staffProfile->phone,
            'bio' => $validated['bio'] ?? $staffProfile->bio,
            'designation_id' => $validated['designation_id'] ?? $staffProfile->designation_id,
            'experience_years' => isset($validated['experience_years'])
                ? (int) $validated['experience_years']
                : $staffProfile->experience_years,
            'specialization' => $validated['specialization'] ?? $staffProfile->specialization,
            'license_number' => $validated['license_number'] ?? $staffProfile->license_number,
            'working_since' => $validated['working_since'] ?? $staffProfile->working_since,
        ], fn ($value) => ! is_null($value)));
    }

    private function getProvider(User $user): string
    {
        return is_object($user->provider)
            ? $user->provider->value
            : ($user->provider ?? 'local');
    }
}
