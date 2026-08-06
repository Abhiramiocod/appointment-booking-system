<?php

namespace App\Actions\Admin\Staff;

use App\Enums\UserRole;
use App\Models\User;

class GetStaffAction
{
    public function execute(array $filters)
    {
        return User::query()
            ->where('role', UserRole::STAFF)

            ->with([
                'staffProfile.designation',
                'services',
            ])

            ->when(
                ! empty($filters['search']),
                function ($query) use ($filters) {
                    $search = $filters['search'];

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    });
                }
            )

            ->orderBy(
                $filters['sort_by'] ?? 'name',
                $filters['sort_dir'] ?? 'asc'
            )

            ->paginate(
                $filters['per_page'] ?? 15
            );
    }
}
