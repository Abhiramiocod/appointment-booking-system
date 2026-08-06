<?php

namespace App\Actions\Admin\Analytics;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GetAnalyticsAction
{
    public function execute(): array
    {
        // General Metrics
        $totalCustomers = User::where('role', UserRole::CUSTOMER)->count();
        $totalStaff = User::where('role', UserRole::STAFF)->count();

        $totalProfit = Appointment::where('status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        $completedCount = Appointment::where('status', 'completed')->count();
        $confirmedCount = Appointment::where('status', 'confirmed')->count();
        $pendingCount = Appointment::where('status', 'pending')->count();
        $cancelledCount = Appointment::where('status', 'cancelled')->count();

        // Monthly Earnings
        $monthlyEarnings = Appointment::where('status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select(
                DB::raw("TO_CHAR(appointment_date, 'YYYY-MM') as month"),
                DB::raw('SUM(services.price) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->limit(6)
            ->get();

        // Top Services
        $topServices = Appointment::select('service_id', DB::raw('COUNT(*) as bookings_count'))
            ->with([
                'service:id,name,price'
            ])
            ->groupBy('service_id')
            ->orderByDesc('bookings_count')
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

        // Staff Performance
        $staffPerformance = User::where('role', UserRole::STAFF)
            ->withCount([
                'staffAppointments as bookings_count'
            ])
            ->withAvg('receivedReviews as avg_rating', 'rating')
            ->get()
            ->map(function ($user) {
                $revenue = Appointment::where('staff_id', $user->id)
                    ->where('status', 'completed')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

                return [
                    'name' => $user->name,
                    'bookings' => $user->bookings_count,
                    'rating' => round($user->avg_rating ?? 5.0, 1),
                    'revenue' => round($revenue, 2),
                    'avatar' => $user->image
                        ? asset($user->image)
                        : null,
                ];
            })
            ->sortByDesc('bookings')
            ->take(5)
            ->values();

        return [
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
        ];
    }
}