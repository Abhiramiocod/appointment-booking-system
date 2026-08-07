<?php

namespace App\Actions\Admin\StaffApplications;

use App\Models\StaffApplication;

class GetStaffApplicationsAction
{
    public function execute(array $filters)
    {
        return StaffApplication::query()
            ->when(
                ! empty($filters['status']),
                fn ($query) => $query->where('status', $filters['status'])
            )

            ->when(
                ! empty($filters['search']),
                function ($query) use ($filters) {
                    $search = trim($filters['search']);

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%")
                            ->orWhere('phone', 'ILIKE', "%{$search}%");
                    });
                }
            )

            ->latest()

            ->paginate(
                $filters['per_page'] ?? 15
            );
    }
}
