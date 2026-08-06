<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Admin\Appointments\GetAppointmentsAction;
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
    public function index(IndexAppointmentRequest $request, GetAppointmentsAction $action)
    {
        Gate::authorize('viewAnyAdmin', Appointment::class);

        $appointments = $action->execute($request);

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
