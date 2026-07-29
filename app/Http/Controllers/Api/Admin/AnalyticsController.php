<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Calculate General Metrics
        $totalCustomers = User::where('role', UserRole::CUSTOMER)->count();
        $totalStaff = User::where('role', UserRole::STAFF)->count();

        // Profit is sum of service price for completed appointments
        $totalProfit = Appointment::where('status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        $completedCount = Appointment::where('status', 'completed')->count();
        $confirmedCount = Appointment::where('status', 'confirmed')->count();
        $pendingCount = Appointment::where('status', 'pending')->count();
        $cancelledCount = Appointment::where('status', 'cancelled')->count();

        // 2. Earnings Over Time (Last 6 Months) - PGSQL compatible
        $monthlyEarnings = Appointment::where('status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select(
                DB::raw("TO_CHAR(appointment_date, 'YYYY-MM') as month"),
                DB::raw('SUM(services.price) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->limit(6)
            ->get();

        // 3. Top Services by Bookings & Revenue
        $topServices = Appointment::select('service_id', DB::raw('count(*) as bookings_count'))
            ->with(['service' => function ($query) {
                $query->select('id', 'name', 'price');
            }])
            ->groupBy('service_id')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->service?->name ?? 'Unknown Service',
                    'price' => $item->service?->price ?? 0,
                    'bookings' => $item->bookings_count,
                    'revenue' => ($item->service?->price ?? 0) * $item->bookings_count,
                ];
            });

        // 4. Staff Performance (Average Rating, Bookings & Revenue)
        $staffPerformance = User::where('role', UserRole::STAFF)
            ->withCount(['staffAppointments as bookings_count'])
            ->withAvg('receivedReviews as avg_rating', 'rating')
            ->get()
            ->map(function ($user) {
                // Calculate actual completed appointment revenue
                $revenue = Appointment::where('staff_id', $user->id)
                    ->where('status', 'completed')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

                return [
                    'name' => $user->name,
                    'bookings' => $user->bookings_count,
                    'rating' => round($user->avg_rating ?? 5.0, 1),
                    'revenue' => round($revenue, 2),
                    'avatar' => $user->image ? asset($user->image) : null,
                ];
            })
            ->sortByDesc('bookings')
            ->take(5)
            ->values();

        return response()->json([
            'metrics' => [
                'total_customers' => $totalCustomers,
                'total_staff' => $totalStaff,
                'total_profit' => round($totalProfit, 2),
                'completed_appointments' => $completedCount,
                'confirmed_appointments' => $confirmedCount,
                'pending_appointments' => $pendingCount,
                'cancelled_appointments' => $cancelledCount,
            ],
            'monthly_earnings' => $monthlyEarnings,
            'top_services' => $topServices,
            'staff_performance' => $staffPerformance,
        ]);
    }
}
