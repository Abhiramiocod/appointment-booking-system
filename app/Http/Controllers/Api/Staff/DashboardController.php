<?php

namespace App\Http\Controllers\Api\Staff;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\StaffReview;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $staffId = $request->user()->id;

        $todayCount = Appointment::query()
            ->where('staff_id', $staffId)
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', [AppointmentStatus::CANCELLED, AppointmentStatus::REJECTED])
            ->count();

        $upcomingCount = Appointment::query()
            ->where('staff_id', $staffId)
            ->where('status', AppointmentStatus::CONFIRMED)
            ->whereDate('appointment_date', '>=', today())
            ->count();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $completedThisWeekCount = Appointment::query()
            ->where('staff_id', $staffId)
            ->where('status', AppointmentStatus::COMPLETED)
            ->whereBetween('appointment_date', [$startOfWeek, $endOfWeek])
            ->count();

        $avgRating = StaffReview::query()
            ->where('staff_id', $staffId)
            ->avg('rating');

        return response()->json([
            'today_appointments' => $todayCount,
            'upcoming_appointments' => $upcomingCount,
            'completed_this_week' => $completedThisWeekCount,
            'average_rating' => round($avgRating ?? 0.0, 2),
        ]);
    }
}
