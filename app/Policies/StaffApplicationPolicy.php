<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\StaffApplication;
use App\Models\User;

class StaffApplicationPolicy
{
    /**
     * Admin can view all staff applications.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Admin can view a specific application.
     */
    public function view(User $user, StaffApplication $staffApplication): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Admin can approve/reject applications.
     */
    public function update(User $user, StaffApplication $staffApplication): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Admin can delete applications.
     */
    public function delete(User $user, StaffApplication $staffApplication): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
