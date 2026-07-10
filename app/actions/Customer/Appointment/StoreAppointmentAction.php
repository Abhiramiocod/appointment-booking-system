<?php

namespace App\actions\Customer\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class StoreAppointmentAction
{
    public function execute(
        User $customer,
        int $serviceId,
        int $staffId,
        string $appointmentDate,
        string $startTime,
        ?string $notes = null
    ): Appointment {
        $service = Service::findOrFail($serviceId);
        $staff = User::findOrFail($staffId);

        if (! $staff->services()->where('services.id', $service->id)->exists()) {
            throw new Exception('Staff does not provide this service.');
        }

        $date = Carbon::parse($appointmentDate);
        $dayOfWeek = $date->dayOfWeek;

        $workingHour = $staff->workingHours()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (! $workingHour) {
            throw new Exception('Staff is unavailable on this date.');
        }

        $endTime = Carbon::createFromFormat('H:i', $startTime)
            ->addMinutes($service->duration)
            ->format('H:i:s');

        $alreadyBooked = Appointment::query()
            ->where('staff_id', $staff->id)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('start_time', $startTime.':00')
            ->whereNotIn('status', [
                AppointmentStatus::CANCELLED,
            ])
            ->exists();

        if ($alreadyBooked) {
            throw new Exception('Selected slot is not available.');
        }

        return Appointment::create([
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'service_id' => $service->id,
            'appointment_date' => $appointmentDate,
            'start_time' => $startTime.':00',
            'end_time' => $endTime,
            'status' => AppointmentStatus::PENDING,
            'notes' => $notes,
        ]);
    }
}
