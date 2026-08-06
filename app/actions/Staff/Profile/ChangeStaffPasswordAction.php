<?php

namespace App\actions\Staff\Profile;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class ChangeStaffPasswordAction
{
    public function execute(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new Exception('The current password you entered is incorrect.', 422);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
