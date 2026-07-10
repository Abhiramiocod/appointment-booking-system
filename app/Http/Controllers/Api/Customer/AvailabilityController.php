<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AvailableSlotsRequest;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    public function availableSlots(AvailableSlotsRequest $request, User $staff)
    {
        $service = Service::findOrFail(
            $request->service_id
        );

        // Verify staff provides this service
        if (! $staff->services()->where('services.id', $service->id)->exists()) {
            return response()->json([
                'message' => 'Staff does not provide this service.',
            ], 422);
        }

        $date = Carbon::parse($request->date);

        $workingHour = $staff->workingHours()
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (! $workingHour) {
            return response()->json([
                'data' => [],
            ]);
        }

        $duration = $service->duration;

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
            $slots[] = $start->format('H:i');

            $start->addMinutes($duration);
        }

        $bookedSlots = Appointment::query()
            ->where('staff_id', $staff->id)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', [
                AppointmentStatus::CANCELLED,
            ])
            ->pluck('start_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();

        $availableSlots = array_values(
            array_diff($slots, $bookedSlots)
        );

        return response()->json([
            'data' => $availableSlots,
        ]);
    }
}
