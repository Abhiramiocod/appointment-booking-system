<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function index(IndexAppointmentRequest $request)
    {
        Gate::authorize('viewAnyAdmin', Appointment::class);

        $query = Appointment::query()
            ->with(['customer', 'staff', 'service']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->has('appointment_date')) {
            $query->whereDate('appointment_date', $request->appointment_date);
        }

        $sortBy = $request->sort_by ?? 'created_at';
        $sortDir = $request->sort_dir ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $appointments = $query->paginate();

        return AppointmentResource::collection($appointments);
    }

    public function show(Appointment $appointment)
    {
        Gate::authorize('viewAdmin', $appointment);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }
}
