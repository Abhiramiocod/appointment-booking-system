<?php

namespace App\Actions\Admin\Staff;

use App\Enums\UserRole;
use App\Models\User;

class SearchStaffAction
{
    public function execute(array $filters)
    {
        return User::query()
            ->where('role', UserRole::STAFF)

            ->when(
                ! empty($filters['search']),
                function ($query) use ($filters) {
                    $search = trim($filters['search']);

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    });
                }
            )

            ->orderBy('name')
            ->limit(5)
            ->get([
                'id',
                'name',
            ]);
    }
}
