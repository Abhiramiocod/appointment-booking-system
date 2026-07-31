<?php

namespace App\actions\Auth;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateProfileAction
{
    public function execute(User $user, array $validated): User
    {
        return DB::transaction(function () use ($user, $validated) {
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
        });
    }

    private function handleAvatar(User $user, array &$validated): void
    {
        $diskName = config('filesystems.default', 's3');
        $disk = Storage::disk($diskName);

        if (! empty($validated['remove_avatar'])) {
            if ($user->image && ! str_starts_with($user->image, 'http')) {
                try {
                    if ($disk->exists($user->image)) {
                        $disk->delete($user->image);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed deleting user avatar from storage disk [{$diskName}]: ".$e->getMessage(), [
                        'user_id' => $user->id,
                        'image' => $user->image,
                    ]);
                }
            }

            $user->image = null;
            $user->save();

            return;
        }

        if (
            isset($validated['avatar']) &&
            $validated['avatar'] instanceof UploadedFile
        ) {
            $oldImage = $user->image;

            try {
                // Upload new image to disk (avatars directory)
                $path = $validated['avatar']->store('avatars', $diskName);

                if (! $path) {
                    throw new \RuntimeException("Storage disk [{$diskName}] failed to return path.");
                }

                // Delete old image if upload succeeded
                if ($oldImage && ! str_starts_with($oldImage, 'http')) {
                    try {
                        if ($disk->exists($oldImage)) {
                            $disk->delete($oldImage);
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed deleting old user avatar from disk [{$diskName}]: ".$e->getMessage(), [
                            'user_id' => $user->id,
                            'old_image' => $oldImage,
                        ]);
                    }
                }

                $user->image = $path;
                $user->save();
            } catch (\Exception $e) {
                Log::error("Failed uploading user avatar to storage disk [{$diskName}]: ".$e->getMessage(), [
                    'user_id' => $user->id,
                    'exception' => $e,
                ]);

                throw $e;
            }
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
