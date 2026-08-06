<?php

namespace App\Actions\Admin\StaffApplication;

use App\Enums\StaffApplicationStatus;
use App\Models\StaffApplication;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RejectStaffApplicationAction
{
    public function execute(StaffApplication $staffApplication, array $data): array
    {

        if ($staffApplication->status !== StaffApplicationStatus::PENDING) {
            throw new HttpException(
                422,
                'This application has already been processed.'
            );
        }

        $staffApplication->update([
            'status' => StaffApplicationStatus::REJECTED,
            'admin_notes' => $data['admin_notes'] ?? null,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
        ]);

        return [
            'message' => 'Application rejected successfully.',
        ];
    }
}
