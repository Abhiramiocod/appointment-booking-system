<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $minBookings = $request->input('min_bookings');
        $sortBy = $request->input('sort_by', 'name'); // 'name', 'bookings_count', 'created_at'

        return User::query()
            ->where('role', UserRole::CUSTOMER)
            ->withCount(['customerAppointments as total_bookings'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($minBookings !== null, function ($query) use ($minBookings) {
                $query->has('customerAppointments', '>=', (int) $minBookings);
            })
            ->when($sortBy === 'bookings_count', function ($query) {
                $query->orderBy('total_bookings', 'desc');
            })
            ->when($sortBy === 'created_at', function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->when($sortBy === 'name', function ($query) {
                $query->orderBy('name', 'asc');
            })
            ->get();
    }
}
