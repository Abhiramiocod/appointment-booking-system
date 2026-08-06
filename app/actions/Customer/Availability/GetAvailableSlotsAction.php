<?php

namespace App\actions\Customer\Availability;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GetAvailableSlotsAction
{
    public function execute(User $staff, array $data): array
    {
        $service = Service::findOrFail($data['service_id']);

        if (! $staff->services()->whereKey($service->id)->exists()) {
            throw new HttpException(
                422,
                'Staff does not provide this service.'
            );
        }

        $date = Carbon::parse($data['date']);

        $workingHour = $staff->workingHours()
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (! $workingHour) {
            return [];
        }

        $slots = $this->generateSlots(
            $date,
            $workingHour,
            $service->duration
        );

        $bookedSlots = Appointment::query()
            ->where('staff_id', $staff->id)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', [
                AppointmentStatus::CANCELLED,
            ])
            ->pluck('start_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();

        return array_values(
            array_diff($slots, $bookedSlots)
        );

    }

    private function generateSlots(
        Carbon $date,
        WorkingHour $workingHour,
        int $duration
    ): array {
        $slots = [];

        $start = Carbon::parse(
            $date->toDateString().' '.$workingHour->start_time
        );

        $end = Carbon::parse(
            $date->toDateString().' '.$workingHour->end_time
        );

        while (
            $start->copy()->addMinutes($duration)->lte($end)
        ) {
            $slotStart = $start->copy();
            $slotEnd = $start->copy()->addMinutes($duration);

            $overlapsBreak = false;
            $breaks = $workingHour->breaks ?? [];
            foreach ($breaks as $break) {
                if (empty($break['start_time']) || empty($break['end_time'])) {
                    continue;
                }
                $breakStart = Carbon::parse($date->toDateString().' '.$break['start_time']);
                $breakEnd = Carbon::parse($date->toDateString().' '.$break['end_time']);

                if ($slotStart->lt($breakEnd) && $slotEnd->gt($breakStart)) {
                    $overlapsBreak = true;
                    break;
                }
            }

            if (! $overlapsBreak) {
                $slots[] = $start->format('H:i');
            }

            $start->addMinutes($duration);
        }

        return $slots;
    }
}
