<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customerId = $request->user()->id;

        $upcomingCount = Appointment::query()
            ->where('customer_id', $customerId)
            ->where('appointment_date', '>=', today())
            ->whereNotIn('status', [AppointmentStatus::CANCELLED, AppointmentStatus::COMPLETED])
            ->count();

        $completedCount = Appointment::query()
            ->where('customer_id', $customerId)
            ->where('status', AppointmentStatus::COMPLETED)
            ->count();

        $cancelledCount = Appointment::query()
            ->where('customer_id', $customerId)
            ->where('status', AppointmentStatus::CANCELLED)
            ->count();

        return response()->json([
            'upcoming_appointments' => $upcomingCount,
            'completed_appointments' => $completedCount,
            'cancelled_appointments' => $cancelledCount,
        ]);
    }
}
