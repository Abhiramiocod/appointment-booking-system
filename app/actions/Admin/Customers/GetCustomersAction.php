<?php

namespace App\Actions\Admin\Customer;

use App\Enums\UserRole;
use App\Models\User;

class GetCustomersAction
{
    public function execute(array $filters)
    {
        return User::query()
            ->where('role', UserRole::CUSTOMER)
            ->withCount([
                'customerAppointments as total_bookings',
            ])

            ->when(
                ! empty($filters['search']),
                function ($query) use ($filters) {
                    $search = $filters['search'];

                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    });
                }
            )

            ->when(
                isset($filters['min_bookings']),
                fn ($query) => $query->has(
                    'customerAppointments',
                    '>=',
                    (int) $filters['min_bookings']
                )
            )

            ->when(
                ($filters['sort_by'] ?? 'name') === 'bookings_count',
                fn ($query) => $query->orderBy('total_bookings', 'desc')
            )

            ->when(
                ($filters['sort_by'] ?? 'name') === 'created_at',
                fn ($query) => $query->orderBy('created_at', 'desc')
            )

            ->when(
                ($filters['sort_by'] ?? 'name') === 'name',
                fn ($query) => $query->orderBy('name', 'asc')
            )

            ->get();
    }
}
