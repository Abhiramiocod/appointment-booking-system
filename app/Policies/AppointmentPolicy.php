<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAnyCustomer(User $user): bool
    {
        return true;
    }

    public function viewCustomer(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->customer_id;
    }

    public function cancelCustomer(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->customer_id;
    }

    public function viewAnyStaff(User $user): bool
    {
        return true;
    }

    public function viewStaff(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->staff_id;
    }

    public function confirmStaff(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->staff_id;
    }

    public function completeStaff(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->staff_id;
    }

    public function cancelStaff(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->staff_id;
    }

    public function viewAnyAdmin(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    public function viewAdmin(User $user, Appointment $appointment): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    public function updateAdmin(User $user, Appointment $appointment): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    public function deleteAdmin(User $user, Appointment $appointment): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
