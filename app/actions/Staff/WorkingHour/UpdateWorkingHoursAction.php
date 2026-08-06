<?php

namespace App\actions\Staff\WorkingHour;

use App\Models\User;
use App\Models\WorkingHour;

class UpdateWorkingHoursAction
{
    public function execute(User $staff, array $workingHours): void
    {
        foreach ($workingHours as $hour) {
            WorkingHour::updateOrCreate(
                [
                    'staff_id' => $staff->id,
                    'day_of_week' => $hour['day_of_week'],
                ],
                [
                    'start_time' => $hour['is_available']
                        ? $hour['start_time']
                        : null,

                    'end_time' => $hour['is_available']
                        ? $hour['end_time']
                        : null,

                    'is_available' => $hour['is_available'],
                    'breaks' => $hour['is_available']
                        ? ($hour['breaks'] ?? [])
                        : [],
                ]
            );
        }
    }
}
