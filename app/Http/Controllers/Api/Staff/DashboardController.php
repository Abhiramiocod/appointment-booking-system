<?php

namespace App\Http\Controllers\Api\Staff;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $staffId = $request->user()->id;

        $todayCount = Appointment::query()
            ->where('staff_id', $staffId)
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', [AppointmentStatus::CANCELLED])
            ->count();

        $pendingCount = Appointment::query()
            ->where('staff_id', $staffId)
            ->where('status', AppointmentStatus::PENDING)
            ->count();

        $confirmedCount = Appointment::query()
            ->where('staff_id', $staffId)
            ->where('status', AppointmentStatus::CONFIRMED)
            ->count();

        $completedCount = Appointment::query()
            ->where('staff_id', $staffId)
            ->where('status', AppointmentStatus::COMPLETED)
            ->count();

        return response()->json([
            'today_appointments' => $todayCount,
            'pending_appointments' => $pendingCount,
            'confirmed_appointments' => $confirmedCount,
            'completed_appointments' => $completedCount,
        ]);
    }
}
