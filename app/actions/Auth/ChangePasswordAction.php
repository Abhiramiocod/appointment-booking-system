<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordAction
{
    public function execute(User $user, array $validated): array
    {
        $hasPassword = ! empty($user->password);

        if ($hasPassword) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'The current password you entered is incorrect.',
                ]);
            }
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return [
            'user' => $user->fresh(),
            'message' => $hasPassword
                ? 'Password updated successfully!'
                : 'Local password created successfully! You can now log in using either Email & Password or your Social Provider.',
        ];
    }
}
