<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAppointmentRequest;
use App\Http\Requests\Admin\StoreAppointmentRequest;
use App\Http\Requests\Admin\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function index(IndexAppointmentRequest $request)
    {
        Gate::authorize('viewAnyAdmin', Appointment::class);

        $appointments = Appointment::query()
            ->with(['customer', 'staff', 'service'])

            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )

            ->when(
                $request->filled('customer_id'),
                fn ($query) => $query->where('customer_id', $request->customer_id)
            )

            ->when(
                $request->filled('staff_id'),
                fn ($query) => $query->where('staff_id', $request->staff_id)
            )

            ->when(
                $request->filled('service_id'),
                fn ($query) => $query->where('service_id', $request->service_id)
            )

            ->when(
                $request->filled('appointment_date'),
                fn ($query) => $query->whereDate('appointment_date', $request->appointment_date)
            )

            ->when(
                $request->filled('date_from'),
                fn ($query) => $query->whereDate('appointment_date', '>=', $request->date_from)
            )

            ->when(
                $request->filled('date_to'),
                fn ($query) => $query->whereDate('appointment_date', '<=', $request->date_to)
            )

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->search;

                    $query->where(function ($q) use ($search) {
                        $q->whereHas('customer', function ($customer) use ($search) {
                            $customer->where('name', 'ILIKE', "%{$search}%");
                        })
                            ->orWhereHas('staff', function ($staff) use ($search) {
                                $staff->where('name', 'ILIKE', "%{$search}%");
                            })
                            ->orWhereHas('service', function ($service) use ($search) {
                                $service->where('name', 'ILIKE', "%{$search}%");
                            });
                    });
                }
            )

            ->orderBy(
                $request->input('sort_by', 'created_at'),
                $request->input('sort_dir', 'desc')
            )

            ->paginate($request->input('per_page', 15));

        return AppointmentResource::collection($appointments);
    }

    public function store(StoreAppointmentRequest $request)
    {
        Gate::authorize('createAdmin', Appointment::class);

        $appointment = Appointment::create(
            $request->validated()
        );

        return new AppointmentResource(
            $appointment->load([
                'customer',
                'staff',
                'service',
            ])
        );
    }

    public function show(Appointment $appointment)
    {
        Gate::authorize('viewAdmin', $appointment);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        Gate::authorize('updateAdmin', $appointment);

        $appointment->update($request->validated());

        return new AppointmentResource(
            $appointment->fresh()->load(['customer', 'staff', 'service'])
        );
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        Gate::authorize('deleteAdmin', $appointment);

        $appointment->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully.',
        ]);
    }
}
